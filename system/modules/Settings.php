<?php

namespace Mozg\modules;

use Intervention\Image\ImageManager;
use Sura\Filesystem\Filesystem;
use Sura\Http\Request;
use Sura\Http\Response;
use Sura\Support\Status;
use Mozg\classes\Module;

class Settings  extends Module
{

    /**
     * @throws \JsonException
     */
    function change_pass()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $password_old = password_hash((new Request)->textFilter((string)$data['oldpassword']), PASSWORD_DEFAULT);
        $password_new = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        $password_renew = password_hash((new Request)->textFilter((string)$data['repassword']), PASSWORD_DEFAULT);
        $access_token = (new Request)->textFilter((string)$data['access_token']);

        $check_user = $this->db->fetch('SELECT user_password FROM `users` WHERE user_hid = ?', $access_token);
        if ($check_user['user_password']) {
            //check current password
            if (password_verify((string)$data['oldpassword'], $check_user['user_password'])) {
                if ($data['password'] == $data['repassword']) {
                    $this->db->query('UPDATE users SET', [
                        'user_password' => $password_new,
                        'user_hid' => $password_new,
                    ], 'WHERE user_hid = ?', $access_token);

                    $response = array(
                        'status' => Status::OK,
                        'access_token' => $password_new,
                    );
                    (new Response)->_e_json($response);
                } else {
                    $response = array(
                        'status' => Status::BAD_PASSWORD,
                        'pass1' => $data['password'],
                        'pass2' => $data['repassword'],
                    );
                    (new Response)->_e_json($response);
                }

            } else {
                $response = array(
                    'status' => Status::NOT_VALID,
                    'oldpass' => $data['oldpassword'],
                );
                (new Response)->_e_json($response);
            }
        } else {
            $response = array(
                'status' => Status::NOT_USER,
            );
            (new Response)->_e_json($response);
        }
    }

    /**
     * @throws \JsonException
     */
    function change_name()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $first_name = ucfirst((new Request)->textFilter((string)$data['first_name']));
        $last_name = ucfirst((new Request)->textFilter((string)$data['last_name']));
        $access_token = (new Request)->textFilter((string)$data['access_token']);

        $this->db->query('UPDATE users SET', [
            'user_name' => $first_name,
            'user_last_name' => $last_name,
        ], 'WHERE user_hid = ?', $access_token);

        $response = array(
            'status' => Status::OK,
        );
        (new Response)->_e_json($response);

    }

    /**
     * @throws \JsonException
     */
    function change_avatar()
    {
        $access_token = (new Request)->textFilter((string)$_POST['access_token']);
        $check_user = $this->db->fetch('SELECT user_id, user_name, user_photo, user_email, user_last_name, user_group, user_albums_num FROM `users` WHERE user_hid = ?', $access_token);

        if ($check_user['user_id']) {
            //Create user dirs
            $upload_dir = ROOT_DIR . '/public/uploads/users/';
            Filesystem::createDir($upload_dir);
            Filesystem::createDir($upload_dir . $check_user['user_id']);
            Filesystem::createDir($upload_dir . $check_user['user_id'] . '/albums');
            $check_system_albums = $this->db->fetch('SELECT aid, cover FROM `albums` WHERE user_id = ? AND system = 1', $check_user['user_id']);

            if (!$check_system_albums) {
                $hash = md5(md5(time()) . md5($check_user['user_id']) . md5($check_user['user_email']));
                $date_create = date('Y-m-d H:i:s', time());
                $sql_privacy = '';
                $this->db->query('INSERT INTO albums', [
                    'user_id' => $check_user['user_id'],
                    'name' => 'Фотографии со страницы',
                    'descr' => '',
                    'ahash' => $hash,
                    'adate' => $date_create,
                    'position' => '0',
                    'system' => '1',
                    'privacy' => $sql_privacy,
                ]);
                
                $aid_fors = $this->db->getInsertId();
                $this->db->query('UPDATE users SET', [
                    'user_albums_num' => $check_user['user_albums_num'] + 1,
                ], 'WHERE user_id = ?', $check_user['user_id']);
            } else {
                $aid_fors = $check_system_albums['aid'];
            }
            $album_dir = ROOT_DIR . '/public/uploads/users/' . $check_user['user_id'] . '/albums/' . $aid_fors . '/';
            Filesystem::createDir($album_dir);

            //Разрешенные форматы
            $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

            //Данные о фотографии
            $image_tmp = $_FILES['file']['tmp_name'];
            $image_name = to_translit($_FILES['file']['name']);
            $image_rename = substr(md5(time() + random_int(1, 100000)), 0, 15);
            $image_size = $_FILES['file']['size'];
            $array = explode(".", $image_name);
            $type = end($array);

            if (in_array($type, $allowed_files, true)) {
                if ($image_size < 5000000) {
                    $res_type = '.' . $type;
                    $new_photo_type = '.png';
                    $upload_dir = ROOT_DIR . '/public/uploads/users/' . $check_user['user_id'] . '/';
                    if (move_uploaded_file($image_tmp, $upload_dir . $image_rename . $res_type)) {
                        $manager = new ImageManager('gd');
                        //Оригинал
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $image->toPng(100)->save($upload_dir . 'o_' . $image_rename . $new_photo_type);
                        //Копия 50х50
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $image->resize(50, 50)->toPng(70)->save($upload_dir . '50_' . $image_rename . $new_photo_type);
                        //Копия 100х100
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $image->resize(100, 100)->toPng(70)->save($upload_dir . '100_' . $image_rename . $new_photo_type);
                        //Главная фотография
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $image->toPng(90)->save($upload_dir . $image_rename . $new_photo_type);

                        if($res_type !== $new_photo_type)
                        Filesystem::delete($upload_dir . $image_rename . $res_type);

                        //В альбом
                        // $image = $manager->read($upload_dir . $image_rename . $res_type);
                        // $image->toPng(90)->save($album_dir . $image_rename . $new_photo_type);

                        $date = date('Y-m-d H:i:s', time());

                        $position_all = $_SESSION['position_all'] ?? null;
                        if ($position_all) {
                            $position_all = $position_all + 1;
                        } else {
                            $position_all = 100000;
                        }

                        $this->db->query('INSERT INTO photos', [
                            'album_id' => $aid_fors,
                            'photo_name' => $image_rename . $new_photo_type,
                            'user_id' => $check_user['user_id'],
                            'date' => $date,
                            'position' => $position_all,
                            'descr' => '',
                            'comm_num' => '0',
                            'rating_all' => '0',
                            'rating_num' => '0',
                            'rating_max' => '0',
                        ]);
                        $ins_id = $this->db->getInsertId();

                        $check_album = $this->db->fetch('SELECT photo_num FROM `albums` WHERE aid = ?', $aid_fors);
                        if (!$check_system_albums['cover']) {
                            $this->db->query('UPDATE albums SET', [
                                'cover' => '',
                                'photo_num+=' => 1, // note +=
                                'adate' => $date
                            ], 'WHERE aid = ?', $aid_fors);
                        } else {
                            $this->db->query('UPDATE albums SET', [
                                'cover' => $image_rename . $new_photo_type,
                                'photo_num+=' => 1, // note +=
                                'adate' => $date
                            ], 'WHERE aid = ?', $aid_fors);
                        }

                        //Добавляем на стену
                        // $row = $db->super_query("SELECT user_sex FROM `users` WHERE user_id = '{$user_id}'");
                        // if ($row['user_sex'] == 2) {
                        //     $sex_text = 'обновила';
                        // }
                        // else {
                        //     $sex_text = 'обновил';
                        // }

                        // $wall_text = "<div class=\"profile_update_photo\"><a href=\"\" onClick=\"Photo.Profile(\'{$user_id}\', \'{$image_rename}{$res_type}\'); return false\"><img src=\"/uploads/users/{$user_id}/o_{$image_rename}{$res_type}\" style=\"margin-top:3px\"></a></div>";

                        // $wall_text = "<div class=\"profile_update_photo\"><a href=\"/photo{$user_id}_{$ins_id}_{$aid_fors}\" onClick=\"Photo.Show(this.href); return false\"><img src=\"/uploads/users/{$user_id}/o_{$image_rename}{$res_type}\" style=\"margin-top:3px\"></a></div>";


                        // $db->query("INSERT INTO `wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$wall_text}', add_date = '{$server_time}', type = '{$sex_text} фотографию на странице:'");
                        // $dbid = $db->insert_id();

                        // $db->query("UPDATE `users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");

                        //Добавляем в ленту новостей
                        // $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$server_time}'");


                        //remove old photo !NB
                        Filesystem::delete($upload_dir . $check_user['user_photo']);
                        Filesystem::delete($upload_dir . 'o_' . $check_user['user_photo']);
                        Filesystem::delete($upload_dir . '50_' . $check_user['user_photo']);
                        Filesystem::delete($upload_dir . '100_' . $check_user['user_photo']);

                        //Обновляем имя фотки в бд
                        $this->db->query('UPDATE users SET', [
                            'user_photo' => $image_rename . $new_photo_type, // note +=
                            // 'user_wall_id' => $dbid
                        ], 'WHERE user_id = ?', $check_user['user_id']);

                        $config = settings_get();
                        $photo = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/50_' . $image_rename . $new_photo_type;


                        // Cache::mozgClearCacheFile('user_' . $user_id . '/profile_' . $user_id);
                        // Cache::mozgClearCache();
                        $status = Status::OK;
                    } else {
                        $photo = '';
                        $status = Status::BAD;
                    }
                } else {
                    $photo = '';
                    $status = Status::BIG_SIZE;
                }
            } else {
                $photo = '';
                $status = Status::BAD_FORMAT;
            }
        } else {
            $photo = '';
            $status = Status::BAD_USER;//7
            $response = [
                'status' => $status,
                'photo' => $photo,
                'access_token' => $access_token,
            ];
            (new Response)->_e_json($response);
            exit();
        }

        $response = [
            'status' => $status,
            'photo' => $photo,
        ];
        (new Response)->_e_json($response);
    }
}