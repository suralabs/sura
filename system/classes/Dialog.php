<?php

/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\classes;

use Mozg\Security\AntiSpam;
use Sura\Database\Exception\ConnectionException;
use Sura\Database\Exception\DriverException;
use Sura\Support\Status;

/**
 *
 */
class Dialog extends Module
{
    /** @var int ID юзера */
    public int $user_id;

    /**
     * @param int $user_id
     */
    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Отправка сообщения
     *
     * @param int $for_user_id Юзер, которому отправляем сообщение
     * @param int $room_id Группа или 0
     * @param string $msg Сообщение
     * @param string $attach_files Прикрепленный контент к сообщению
     * @return array Номер сообщения, статус, имя пользователя и фото пользователя
     * @throws ConnectionException
     * @throws DriverException
     */
    public function send(int $for_user_id, int $room_id, string $msg, string $attach_files): array
    {
        /** @var array $user_info */
        $user_info = $this->db->fetch('SELECT user_photo, user_name FROM `users` WHERE user_id = ?', $this->user_id);
        $anti_spam = new AntiSpam( $this->user_id);
        if ($anti_spam->check('messages', false)) {
            return array(
                'status' => Status::LIMIT
            );
        }
        if ($room_id) {
            $for_user_id = 0;
        }
        $attach_files = str_replace('vote|', 'hack|', $attach_files);
        if ($anti_spam->check('identical', $msg . $attach_files)) {
            return array(
                'status' => Status::LIMIT
            );
        }

        if (!empty($msg) || !empty($attach_files)) {
            if ($room_id == 0) {
                /** @var array $row */
                $row = $this->db->fetch('SELECT user_privacy FROM `users` WHERE user_id = ?', $for_user_id);
            } else {
                /** @var array $row */
                $row = $this->db->fetch('SELECT id FROM `room_users` WHERE room_id = ? and oid2 = ? and type = 0',
                    $room_id, $this->user_id
                );
            }
            if ($row) {
                if ($room_id == 0) {
                    $user_privacy = unserialize($row['user_privacy']);
                    $check_blacklist = (new Friendship($this->user_id))->checkBlackList($for_user_id);
                    if ($user_privacy['val_msg'] == 2) {
                        $check_friend = (new Friendship($this->user_id))->checkFriends($for_user_id);
                    } else {
                        $check_friend = null;
                    }
                    if (!$check_blacklist and $user_privacy['val_msg'] == 1 or
                        $user_privacy['val_msg'] == 2 and $check_friend) {
                        $privacy = 1;
                    } else {
                        $privacy = 0;
                    }
                } else {
                    $privacy = 1;
                }
                if ($privacy && $this->user_id !== $for_user_id) {
                    $anti_spam->LogInsert('identical', $msg . $attach_files);
                    if (!$room_id && !(new Friendship($this->user_id))->checkBlackList($for_user_id))
                    $anti_spam->LogInsert('messages', false);
                    $user_ids = array();
                    if (!$room_id) {
                        $user_ids[] = $for_user_id;
                        $user_ids[] = $this->user_id;
                    } else {
                        $sqlUsers = $this->db->fetchAll('SELECT oid2 FROM `room_users` WHERE room_id = ? and type = 0',$room_id);
                        foreach ($sqlUsers as $rowUser)
                            $user_ids[] = $rowUser['oid2'];
                    }

                    $this->db->query('INSERT INTO messages', [
                        'user_ids' => implode(',', $user_ids),
                        'text' => $msg,
                        'room_id' => $room_id,
                        'date' => time(),
                        'history_user_id' => $this->user_id,
                        'attach' => $attach_files,
                    ]);

                    $db_id = $this->db->getInsertId();
                    $user_ids = array_diff($user_ids, array($this->user_id));
                    foreach ($user_ids as $k => $v) {
                        $this->db->query('UPDATE users SET', [
                            'user_pm_num+=' => 1,
                        ], 'WHERE user_id = ?', $v);
                        $check_im_2 = $this->db->fetch('SELECT id FROM im WHERE iuser_id = ? AND im_user_id = ? AND room_id = ?',
                            $v, ($room_id ? 0 : $this->user_id), $room_id
                        );
                        if (!$check_im_2) {
                            $this->db->query('INSERT INTO im', [
                                'iuser_id' => $v,
                                'im_user_id' => ($room_id ? 0 : $this->user_id),
                                'room_id' => $room_id,
                                'msg_num' => 1,
                                'idate' => time(),
                                'all_msg_num' => 1,
                            ]);
                        } else {                           
                            $this->db->query('UPDATE im SET', [
                                'idate' => time(),
                                'msg_num+=' => 1,
                                'all_msg_num+=' => 1,
                            ], 'WHERE id = ?', $check_im_2['id']);
                        }
                        /** @var array $check2 */
                        $check2 = $this->db->fetch(
                            "SELECT user_last_visit FROM `users` WHERE user_id = ?", $v);
                        $update_time = time() - 70;
                        if ($check2['user_last_visit'] >= $update_time) {
                            $msg_lnk = '/messages#' . ($room_id ? 'c' . $room_id : $this->user_id);

                            $this->db->query('INSERT INTO updates', [
                                'for_user_id' => $v,
                                'from_user_id' => $this->user_id,
                                'type' => '8',
                                'date' => time(),
                                'text' => $msg,
                                'user_photo' => $user_info['user_photo'],
                                'user_name' => $user_info['user_name'],
                                'lnk' => $msg_lnk,
                            ]);

                            Cache::mozgCreateCache("user_{$v}/updates", 1);
                        }
                        Cache::mozgClearCacheFile('user_' . $v . '/im');
                        Cache::mozgCreateCache('user_' . $v . '/im_update', '1');
                        Cache::mozgCreateCache("user_{$v}/typograf{$this->user_id}", "");
                    }
                    $check_im = $this->db->fetch(
                        "SELECT id FROM `im` WHERE iuser_id = ? AND im_user_id = ? AND room_id = ?",
                        $this->user_id, $for_user_id, $room_id
                    );
                    if (!$check_im) {
                        $this->db->query('INSERT INTO im', [
                            'iuser_id' => $this->user_id,
                            'im_user_id' => $for_user_id,
                            'room_id' => $room_id,
                            //'msg_num' => 1,
                            'idate' => time(),
                            'all_msg_num' => 1,
                        ]);
                    } else {
                        $this->db->query('UPDATE im SET', [
                            'idate' => time(),
                            'all_msg_num+=' => 1,
                        ], 'WHERE id = ?', $check_im['id']);
                    }
                    return [
                        'status' => Status::OK,
                        'id' => $db_id,
                        'user_photo' => $user_info['user_photo'],
                        'user_name' => $user_info['user_name']
                    ];
                }
                return array(
                    'status' => Status::PRIVACY
                );
            }
            return array(
                'status' => Status::NOT_USER
            );
        }

        return array(
            'status' => Status::NOT_VALID
        );

    }

    /**
     * @param int $msg_id
     * @return bool "false" - если не найдено
     * @throws ConnectionException
     * @throws DriverException
     */
    final public function read(int $msg_id): bool
    {
        $check = $this->db->fetch('SELECT id, id2, date, room_id, history_user_id, room_id, read_ids, user_ids 
            FROM `messages` WHERE id = ? AND find_in_set( ? , user_ids) 
            AND not find_in_set( ? , del_ids) AND not find_in_set( ? , read_ids) 
            AND history_user_id != ?', $msg_id, $this->user_id, $this->user_id, $this->user_id, $this->user_id);
        if ($check) {
            $read_ids = explode(',', $check['read_ids']);
            $read_ids[] = $this->user_id;
            $this->db->query('UPDATE messages SET', [
                'read_ids' => implode(',', $read_ids),
            ], 'WHERE id = ?', $check['id']);

            $this->db->query('UPDATE users SET', [
                'user_pm_num-=' => 1,
            ], 'WHERE user_id = ?', $this->user_id);

            if (!$check['room_id']) {
                $user_ids = explode(',', $check['user_ids']);
                $im_user_id = $user_ids[0] == $this->user_id ? $user_ids[1] : $user_ids[0];
            } else {
                $im_user_id = 0;
            }
            $this->db->query('UPDATE im SET', [
                'user_pm_num-=' => 1,
            ], 'WHERE iuser_id = ? AND im_user_id = ? AND room_id', $this->user_id, $im_user_id, $check['room_id']);
            Cache::mozgClearCacheFile('user_' . $check['history_user_id'] . '/im');
            return true;
        }
        return false;
    }

    /**
     * @throws \ErrorException
     */
    final public function typograf(int $room_id, int $for_user_id, string $action): bool
    {
        if ($room_id === 0) {
            if ($action === 'start') {
                Cache::mozgCreateCache("user_{$for_user_id}/typograf{$this->user_id}", "");
                return true;
            }
            if ($action === 'stop') {
                Cache::mozgCreateCache("user_{$for_user_id}/typograf{$this->user_id}", 1);
                return true;
            }
            throw new \ErrorException('not action');
        }
        return false;
    }

    /**
     * @param int $room_id
     * @param int $im_user_id
     * @return bool
     * @throws ConnectionException
     * @throws DriverException
     */
    final public function delete(int $room_id, int $im_user_id): bool
    {
        if ($room_id > 0) {
            $im_user_id = 0;
        }
        $row = $this->db->fetch('SELECT id, msg_num, all_msg_num FROM `im` WHERE iuser_id = ? AND im_user_id = ? AND room_id = ?',
            $this->user_id, $im_user_id, $room_id);
        if ($row) {
            //todo update query
            $sql = $this->db->fetchAll('SELECT id, read_ids, room_id, history_user_id, del_ids FROM `messages` 
                WHERE ' .
                ($room_id ? "room_id = '{$room_id}'" : "room_id = 0 and find_in_set('{$im_user_id}', tb1.user_ids)") .
                ' AND find_in_set(?, user_ids) AND not find_in_set(?, del_ids)', $this->user_id, $this->user_id);
            if ($sql) {
                foreach ($sql as $row2) {
                    $del_ids = $row2['del_ids'] ? explode(',', $row2['del_ids']) : array();
                    $del_ids[] = $this->user_id;
                    $del_ids = implode(',', $del_ids);

                    $this->db->query('UPDATE messages SET', [
                        'del_ids' => $del_ids,
                    ], 'WHERE id = ?', $row2['id']);

                    $read_ids = explode(',', $row2['read_ids']);
                    if ($row['history_user_id'] !== $this->user_id && !in_array($this->user_id, $read_ids, true)) {
                        $read_ids[] = $this->user_id;
                        $this->db->query('UPDATE messages SET', [
                            'read_ids' => implode(',', $read_ids),
                        ], 'WHERE id = ?', $row2['id']);

                        $this->db->query('UPDATE users SET', [
                            'user_pm_num-=' => 2,
                        ], 'WHERE user_id = ?', $this->user_id);

                        if (!$row2['room_id']) {
                            $user_ids = explode(',', $row2['user_ids']);
                            $im_user_id = $user_ids[0] === $this->user_id ? $user_ids[1] : $user_ids[0];
                        } else {
                            $im_user_id = 0;
                        }
                        $this->db->query('UPDATE im SET', [
                            'msg_num-=' => 1,
                        ], 'WHERE user_id = ? AND im_user_id = ? AND room_id = ?', $this->user_id, $im_user_id, $row2['room_id']);
                        
                        Cache::mozgClearCacheFile('user_' . $row2['history_user_id'] . '/im');
                    }
                }
            }
            if ($row['msg_num']) {
                $this->db->query('UPDATE users SET', [
                    'user_pm_num-=' => $row['msg_num'],
                ], 'WHERE user_id = ?', $this->user_id);

            }
            $this->db->query('DELETE FROM im WHERE id = ?', $row['id']);
            return true;
        }
        return false;
    }
}