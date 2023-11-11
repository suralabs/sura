<?php

namespace Mozg\modules;

use Mozg\classes\{Friendship, Module, Notify};
use Sura\Http\{Request, Response};
use Sura\Support\Status;

class Friends extends Module
{

    /**
     * @return void
     * @throws \JsonException
     * @throws \Sura\Database\Exception\ConnectionException
     * @throws \Sura\Database\Exception\DriverException
     */
    public function add()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
        $for_user_id = (int)$data['user'];
        $from_user_id = $check_user['user_id'];

        //Проверяем существования заявки у себя в заявках
        $check = $this->db->fetch('SELECT for_user_id FROM `friends_demands` WHERE for_user_id = ? AND from_user_id = ?', $from_user_id, $for_user_id);
        if (!$check) {

            //Проверяем на факт существования заявки для пользователя, если она уже есть, то даёт ответ ok
            $check_demands = $this->db->fetch('SELECT for_user_id FROM `friends_demands` WHERE for_user_id = ? AND from_user_id = ?', $for_user_id, $from_user_id);
            if ($for_user_id AND !$check_demands AND $for_user_id != $from_user_id) {

                //Проверяем нет ли этого юзера уже в списке друзей
                $check_friendlist = $this->db->fetch('SELECT user_id FROM `friends` WHERE friend_id = ? AND user_id = ? AND subscriptions = 0', $for_user_id, $from_user_id);
                if (!$check_friendlist) {
                    $this->db->query('INSERT INTO friends_demands', [
                        'for_user_id' => $for_user_id,
                        'from_user_id' => $from_user_id,
                        'demand_date' => time(),
                    ]);
                    $this->db->query('UPDATE users SET', [
                        'user_friends_demands+=' => 1, // note +=
                    ], 'WHERE user_id = ?', $for_user_id);

                    //Вставляем событие в моментальные оповещения
                    (new Notify)->add($for_user_id, $from_user_id, Notify::FRIENDS_SEND);
                    $config = settings_get();
                    //Отправка уведомления на E-mail
                    if ($config['news_mail_1'] == 'yes') {
                        $row_user_email = $this->db->fetch('SELECT user_name, user_email FROM `users` WHERE user_id = ?', $for_user_id);
                        if ($row_user_email['user_email']) {
                            // include_once ENGINE_DIR . '/classes/mail.php';
                            // $mail = new vii_mail($config);
                            // $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '" . $user_id . "'");
                            // $rowEmailTpl = $db->super_query("SELECT text FROM `mail_tpl` WHERE id = '1'");
                            // $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
                            // $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
                            // $mail->send($rowUserEmail['user_email'], 'Новая заявка в друзья', $rowEmailTpl['text']);
                            // Email::send($email, $dictionary['lost_subj'], $message);
                        }
                    }
                    $response = array(
                        'status' => Status::OK,
                    );
                } else{
                    $response = array(
                        'status' => Status::OK,
                    );       
                    // echo 'yes_friend';             
                }
            }else{
                $response = array(
                    'status' => Status::BAD,
                );  
            }
        } else{
            //Добавляем юзера который кинул заявку в список друзей
            $this->db->query('INSERT INTO friends', [
                'user_id' => $from_user_id,
                'friend_id' => $for_user_id,
                'friends_date' => time(),
            ]);

            //Тому кто предлогал дружбу, добавляем ему в друзья себя
            $this->db->query('INSERT INTO friends', [
                'user_id' => $for_user_id,
                'friend_id' => $from_user_id,
                'friends_date' => time(),
            ]);
            
            //Обновляем кол-во заявок и кол-друзей у юзера
            $this->db->query('UPDATE users SET', [
                'user_friends_demands-=' => 1, // note +=
                'user_friends_num+=' => 1, // note +=
            ], 'WHERE user_id = ?', $from_user_id);

            //Тому кто предлогал дружбу, обновляем кол-друзей
            $this->db->query('UPDATE users SET', [
                'user_friends_num+=' => 1, // note +=
            ], 'WHERE user_id = ?', $for_user_id);

            //Удаляем заявку из таблицы заявок
            $this->db->query('DELETE FROM friends_demands WHERE for_user_id = ? AND from_user_id = ?', $from_user_id, $for_user_id);
            $generateLastTime = time() - 10800;

            //Добавляем действия в ленту новостей кто подавал заявку
            // $rowX = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 4 AND ac_user_id = '{$for_user_id}'");
            // if ($rowX['ac_id'])
            //     if (!preg_match("/{$rowX['action_text']}/i", $from_user_id))
            //         $db->query("UPDATE `news` SET action_text = '{$rowX['action_text']}||{$from_user_id}', action_time = '{time()}' WHERE ac_id = '{$rowX['ac_id']}'");
            //     else
            //         echo '';
            // else
            //     $db->query("INSERT INTO `news` SET ac_user_id = '{$for_user_id}', action_type = 4, action_text = '{$from_user_id}', action_time = '{time()}'");

            //Вставляем событие в моментальные оповещения
            (new Notify)->add($for_user_id, $from_user_id, Notify::UPDATE, $text = '', $lnk = '');

            //add to cache
            (new Friendship($from_user_id))->addFriend($for_user_id);

            //Добавляем действия в ленту новостей себе
            // $row = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 4 AND ac_user_id = '{$from_user_id}'");
            // if ($row)
            //     if (!preg_match("/{$row['action_text']}/i", $for_user_id))
            //         $db->query("UPDATE `news` SET action_text = '{$row['action_text']}||{$for_user_id}', action_time = '{time()}' WHERE ac_id = '{$row['ac_id']}'");
            //     else
            //         echo '';
            // else
            //     $db->query("INSERT INTO `news` SET ac_user_id = '{$from_user_id}', action_type = 4, action_text = '{$for_user_id}', action_time = '{time()}'");

            $response = array(
                'status' => Status::OK,
            );
            // echo 'yes_demand2';            
        }
            (new Response)->_e_json($response);
    }

    public function delete()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
        $for_user_id = (int)$data['user'];
        $from_user_id = $check_user['user_id'];

        //Проверяем на существования юзера в списке друзей
        $check = $this->db->fetch('SELECT user_id FROM `friends` WHERE user_id = ? AND friend_id = ? AND subscriptions = 0', $from_user_id, $for_user_id);
        if ($check) {
            //Удаляем друга из таблицы друзей
            $this->db->query('DELETE FROM friends WHERE user_id = ? AND friend_id = ? AND subscriptions = 0', $from_user_id, $for_user_id);
            //Удаляем у друга из таблицы
            $this->db->query('DELETE FROM friends WHERE user_id = ? AND friend_id = ? AND subscriptions = 0', $for_user_id, $from_user_id);

            //Обновляем кол-друзей у юзера
            $this->db->query('UPDATE users SET', [
                'user_friends_num-=' => 1, // note +=
            ], 'WHERE user_id = ?', $from_user_id);
            //Обновляем у друга которого удаляем кол-во друзей
            $this->db->query('UPDATE users SET', [
                'user_friends_num-=' => 1, // note +=
            ], 'WHERE user_id = ?', $for_user_id);

            (new Friendship($from_user_id))->removeFriend($for_user_id);
        }else{
            //Проверяем на существования юзера в таблице заявок в друзья
            $check = $this->db->fetch('SELECT for_user_id FROM `friends_demands` WHERE for_user_id = ? AND from_user_id = ?', $from_user_id, $for_user_id);
            if ($check) {
                //Обновляем кол-во заявок у юзера
                $this->db->query('UPDATE users SET', [
                    'user_friends_num-=' => 1, // note +=
                ], 'WHERE user_id = ?', $from_user_id);
                //Удаляем заявку из таблицы заявок
                $this->db->query('DELETE FROM friends_demands WHERE for_user_id = ? AND from_user_id = ?', $from_user_id, $for_user_id);
            } else{
                // echo 'no_request';
            }
                
        }        
    }

    public function all()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $page = $data['page'] ?? 1;
        $g_count = 20;
        $limit_page = ($page - 1) * $g_count;
        $access_token = (new Request)->textFilter((string)$data['access_token']);        
        $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);

        $for_user = $data['user'];

        $row_for_user = $this->db->fetch('SELECT user_name, user_friends_num FROM `users` WHERE user_id = ?', $for_user);
        if ($row_for_user['user_friends_num']) {
            if ($for_user == $check_user['user_id']){
                $sql_order = "ORDER by `views`";
            }else{
                $sql_order = "ORDER by `friends_date`"; 
            }
            
            $sql_ = $this->db->fetchAll('SELECT tb1.friend_id, tb2.user_birthday, user_photo, user_name, user_last_name, user_country_city_name, user_last_visit 
            FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = ? AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 '.$sql_order.' DESC LIMIT '.$limit_page.', '.$g_count, $for_user);
    
            $items = $this->buildList($sql_);

            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'items' => $items,
                ),
            );
            
        }else{
            $response = array(
                'status' => Status::BAD,
            );
        }

        (new Response)->_e_json($response); 
    }

    public function search()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        // $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    }

    public function requests()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $page = $data['page'];
        $g_count = 20;
        $limit_page = ($page - 1) * $g_count;
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = $this->db->fetch('SELECT user_id, user_friends_demands FROM `users` WHERE user_hid = ?', $access_token);
        // $for_user = $data['user'];
        $for_user = $check_user['user_id'];

        // $row_for_user = $this->db->fetch('SELECT user_name, user_friends_num FROM `users` WHERE user_id = ?', $for_user);

        if ($check_user['user_friends_demands']) {
            $sql_ = $this->db->fetchAll('SELECT tb1.from_user_id, demand_date, tb2.user_photo, user_name, user_last_name, user_country_city_name, user_birthday 
            FROM `friends_demands` tb1, `users` tb2 WHERE tb1.for_user_id = ? AND tb1.from_user_id = tb2.user_id ORDER by `demand_date` DESC LIMIT '.$limit_page.', '.$g_count, $for_user);

            $items = $this->buildList($sql_);

            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'items' => $items,
                ),
            );
        }else{
            $response = array(
                'status' => Status::BAD,
            );
        }

        (new Response)->_e_json($response); 
    }

    public function online()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $page = $data['page'] ?? 1;
        $g_count = 20;
        $limit_page = ($page - 1) * $g_count;
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $for_user = $data['user'];
        // $row_for_user = $this->db->fetch('SELECT user_name, user_friends_num FROM `users` WHERE user_id = ?', $for_user);
        $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
       
        $online_time = time() - 150;
        if ($for_user == $check_user['user_id']){
            $sql_order = "ORDER by `views`";
        }else{
            $sql_order = "ORDER by `friends_date`"; 
        }
        $sql_ = $this->db->fetchAll('SELECT tb1.user_id, user_country_city_name, user_name, user_last_name, user_birthday, user_photo FROM `users` tb1, `friends` tb2 
        WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = ?  AND tb1.user_last_visit >= '.$online_time.' AND tb2.subscriptions = 0 '.$sql_order.' DESC LIMIT '.$limit_page.', '.$g_count, $for_user);
        if ($sql_){
            //Кол-во друзей в онлайн
            $online_friends = $this->db->fetch('SELECT COUNT(*) AS cnt FROM `users` tb1, `friends` tb2 
            WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = ? AND tb1.user_last_visit >= '.$online_time.' AND tb2.subscriptions = 0', $for_user);  
            
            $items = $this->buildList($sql_);

            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'items' => $items,
                    'online' => $online_friends['cnt'],
                ),
            );     
        }else{
            $response = array(
                'status' => Status::BAD,
            );
        }
        (new Response)->_e_json($response); 
    }

    /**
     * Summary of common
     * @return void
     */
    public function common()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $page = (int)$data['page'];
        $g_count = 20;
        $limit_page = ($page - 1) * $g_count;
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
        $for_user = (int)$data['user'];
        $row_for_user = $this->db->fetch('SELECT user_name, user_friends_num FROM `users` WHERE user_id = ?', $for_user);

        if ($row_for_user['user_friends_num'] and $for_user != $check_user['user_id']) {
            $sql_ = $this->db->fetchAll('SELECT tb1.friend_id, tb3.user_birthday, user_photo, user_name, user_last_name, user_country_city_name, user_last_visit 
            FROM `users` tb3, `friends` tb1 
            WHERE tb1.user_id = ? AND tb2.friend_id = ? AND tb1.subscriptions = 0 AND tb2.subscriptions = 0 AND tb1.friend_id = tb3.user_id ORDER by `friends_date`
            LIMIT '.$limit_page.', '.$g_count, $check_user['user_id'], $for_user);
            if ($sql_){
                $count_common = $this->db->fetch('SELECT COUNT(*) AS cnt FROM `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id 
                WHERE tb1.user_id = ? AND tb2.friend_id = ? AND tb1.subscriptions = 0 AND tb2.subscriptions = 0', $check_user['user_id'], $for_user);

                $items = $this->buildList($sql_);
                
                $response = array(
                    'status' => Status::OK,
                    'data' => array(
                        'items' => $items,
                        'count' => $count_common['cnt'],
                    ),
                );  
            }else{
                $response = array(
                    'status' => Status::BAD,
                );
            }
            (new Response)->_e_json($response); 
        }
    }

    public function buildList($sql_)
    {
        $config = settings_get();
        $items = array();
        foreach ($sql_ as $key => $item) {
            $items[$key]['id'] = $item['friend_id'];
            $items[$key]['user_birthday'] = $item['user_birthday'];
            if ($item['user_photo']){
                $items[$key]['photo_50'] = $config['api_url'] . 'uploads/users/' . $item['friend_id'] . '/50_' . $item['user_photo'];
            }else{
                $items[$key]['photo_50'] = $config['api_url'] . 'images/100_no_ava.png';
            }
            $online_time = time() - 150;
            if ($item['user_last_visit'] >= $online_time){
                $items[$key]['online'] = true;
            }else{
                $items[$key]['online'] = false;
            }
            $items[$key]['first_name'] = $item['user_name'];
            $items[$key]['last_name'] = $item['user_last_name'];
        }
        return $items;
    }

}