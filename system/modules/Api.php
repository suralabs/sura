<?php

namespace Mozg\modules;

use JetBrains\PhpStorm\NoReturn;
use \Sura\Http\Response;
use \Sura\Http\Request;
use \Sura\Support\Status;
use Mozg\classes\{DB, Module, ViewEmail, Email};
use Intervention\Image\ImageManager;
use Sura\Filesystem\Filesystem;

class Api  extends Module
{
    /**
     * @throws \JsonException
     */
    final public function main(): void
    {
        $response = array(
            'status' => '1',
        );

        (new \Sura\Http\Response)->_e_json($response);
    }

    /**
     * authorize
     *
     *
     * @throws \JsonException
     */
    final public function authorize()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // var_dump($data);
        // echo $data['email'];
        // echo (new Request)->textFilter($data['email']);
        // exit();
        $email = (new Request)->textFilter((string)$data['email']);
        // $password = md5(md5(stripslashes((new Request)->textFilter($data["password"]))));
        $password = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::NOT_VALID,
                'data' => $email. ' ' . $password,
            );
            (new Response)->_e_json($response);
            exit();
        }
        if (!empty($email)) {
            $check_user = DB::getDB()->row('SELECT user_id, user_password, user_hid FROM `users` WHERE user_email = ?', $email);
            if ($check_user) {
                if (password_verify ($data['password'], $check_user['user_password'])) {
                    $hid = $password;
                    DB::getDB()->update('users', [
                        'user_hid' => $hid
                    ], [
                        'user_id' => $check_user['user_id']
                    ]);
                    DB::getDB()->delete('updates', [
                        'for_user_id' => $check_user['user_id']
                    ]);
                    $response = array(
                        'status' => Status::OK,
                        'access_token' => $hid,
                    );
                    (new Response)->_e_json($response);                    
                }else{
                    $response = array(
                        'status' => Status::BAD_PASSWORD,
                        // 'data1' => $check_user['user_password'],
                        // 'data2' => $password,
                    );
                    (new Response)->_e_json($response);
                }
            }else{
                $response = array(
                    'status' => Status::NOT_USER,
                );
                (new Response)->_e_json($response);
            }
        }else{
         $response = array(
            'status' => Status::NOT_DATA,
        );

        (new Response)->_e_json($response);           
        }
    }

    function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);       
        $name = (new Request)->textFilter((string)$data['firstname']);
        $lastname = (new Request)->textFilter((string)$data['lastname']);
        $email = (new Request)->textFilter((string)$data['email']);
        $pass = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        $repass = password_hash((new Request)->textFilter((string)$data['repassword']), PASSWORD_DEFAULT);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::BAD_MAIL,
                // 'data' => $email. ' ' . $password,
            );
            (new Response)->_e_json($response);
            exit();
        }

        if (!password_verify( $data['password'] , $repass )) {
        // if($data['password'] !== $data['repassword']){
            $response = array(
                'status' => Status::BAD_PASSWORD,//5
                'pass1' => $data['password'],
                'pass2' => $data['repassword'],
            );
            (new Response)->_e_json($response);
            exit();
        }
        
        $_IP = '0.0.0.0';
        $hid = $repass;
        $time = time();
        $server_time = time();
        $check_email = \Mozg\classes\DB::getDB()->row('SELECT COUNT(*) AS cnt FROM `users` WHERE user_email = ?', $email);
        if (!$check_email['cnt']) {
            \Mozg\classes\DB::getDB()->insert('users', [
                'user_last_visit' => $server_time,
                'user_email' => $email,
                'user_password' => $pass,
                'user_name' => $name,
                'user_lastname' => $lastname,
                'user_photo' => '',
                'user_day' => '0',
                'user_month' => '0',
                'user_year' => '0',
                'user_country' => '0',
                'user_city' => '0',
                'user_reg_date' => $server_time,
                'user_lastdate' => $server_time,
                'user_group' => '5',
                'user_hid' => $hid,
                'user_search_pref' => $name . ' ' . $lastname,
                'user_birthday' => '0-0-0',
                'user_privacy' => 'val_msg|1||val_wall1|1||val_wall2|1||val_wall3|1||val_info|1||',
                'user_wall_id' => '0',
                'user_sex' => '0',
                'user_country_city_name' => '',
                'user_xfields' => '',
                'xfields' => '',
                'user_xfields_all' => '',
                'user_albums_num' => '0',
                'user_friends_demands' => '0',
                'user_friends_num' => '0',
                'user_fave_num' => '0',
                'user_pm_num' => '0',
                'user_notes_num' => '0',
                'user_subscriptions_num' => '0',
                'user_videos_num' => '0',
                'user_wall_num' => '0',
                'user_status' => '',
                'user_blacklist_num' => '0',
                'user_blacklist' => '0',
                'user_sp' => '',
                'user_support' => '0',
                'user_balance' => '0',
                'user_lastupdate' => $server_time,
                'user_gifts' => '0',
                'user_public_num' => '0',
                'user_audio' => '0',
                'user_msg_type' => '0',
                'user_delet' => '0',
                'user_ban' => '0',
                'user_ban_date' => '0',
                'user_new_mark_photos' => 0,
                'user_doc_num' => '0',
                'user_logged_mobile' => '0',
                'balance_rub' => '0',
                'user_rating' => '0',
                'invties_pub_num' => '0',
                'user_real' => '0',
                'user_active' => '0',
                'notify' => '0',
            ]);

            $response = array(
                'status' => Status::OK,
                'access_token' => $hid,
            );
    
            (new Response)->_e_json($response);   
        }else{
            $response = array(
                'status' => Status::NOT_DATA,//20
                'data' => $check_email['cnt'],
            );

            (new Response)->_e_json($response);               
        }


    }

    function restore()
    {
        $data = json_decode(file_get_contents('php://input'), true);  
        $email = (new Request)->textFilter((string)$data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::BAD_MAIL,
                // 'data' => $email. ' ' . $password,
            );
            (new Response)->_e_json($response);
            exit();
        }
        $check = $this->db->row('SELECT user_id, user_search_pref, user_photo, user_name FROM `users` WHERE user_email = ?', $email);
        if ($check) {
            //Удаляем все предыдущие запросы на восстановление
            $this->db->delete('restore', [
                'email' => $email
            ]);
            $salt = 'abchefghjkmnpqrstuvwxyz0123456789';
            $rand_lost = '';
            for ($i = 0; $i < 15; $i++) {
                $rand_lost .= $salt[random_int(0, 33)];
            }
            $server_time = time();
            $hash = md5($server_time . $email . random_int(0, 100000) . $rand_lost . $check['user_name']);
            // $hash = random_int(100000, 999999);
            //Вставляем в базу
            $_IP = '';//FIXME
            $this->db->insert('restore', [
                'email' => $email,
                'hash' => $hash,
                'ip' => $_IP,
            ]);
            //Отправляем письмо на почту для восстановления
            $config = settings_get();

            /** @var array $lang */
            $dictionary = $this->lang;
            $variables = [
                'user_name' => $check['user_name'],
                'home_url' => $config['home_url'],
                'site_name' => $config['home'],
                'admin_mail' => $config['admin_mail'],
                'hash' => $hash,
            ];
            $message = (new ViewEmail('restore.email', $variables))->run();
            /** @var ?string $dictionary['lost_subj'] */
            Email::send($email, $dictionary['lost_subj'], $message);

            $response = array(
                'status' => Status::OK,
            );
            (new Response)->_e_json($response);
        } else {
            $response = array(
                'status' => Status::NOT_USER,
            );
            (new Response)->_e_json($response);
        }        
    }

    function reset_password()
    {      
        $data = json_decode(file_get_contents('php://input'), true);  
        $pass = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        $repass = password_hash((new Request)->textFilter((string)$data['repassword']), PASSWORD_DEFAULT);
        $hash = (new Request)->textFilter((string)$data['hash']);
        if (strlen($data['password']) >= 6 and $data['password'] === $data['repassword']) {
            $row = $this->db->row('SELECT email FROM `restore` WHERE hash = ? ', $hash);
            if ($row['email']) {
                $this->db->update('users', [
                    'user_password' => $pass,
                    'user_hid' => $repass,
                ], [
                    'user_email' => $row['email']
                ]);
                $this->db->delete('restore', [
                    'email' => $row['email']
                ]);    
    
                $response = array(
                    'status' => Status::OK,
                    'access_token' => $repass,
                );
                (new Response)->_e_json($response);
                exit();
            }else{
                $response = array(
                    'status' => Status::NOT_VALID,
                    // 'hash' => $hash,
                    // 'email' => print_f($row),
                );
                (new Response)->_e_json($response);
                exit();
            }
        }else{
            $response = array(
                'status' => Status::BAD_PASSWORD,
            );
            (new Response)->_e_json($response);
            exit();
        }
    }

    function change_pass()
    {
        $data = json_decode(file_get_contents('php://input'), true);  

        $password_old = password_hash((new Request)->textFilter((string)$data['oldpassword']), PASSWORD_DEFAULT);
        $password_new = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        $password_renew = password_hash((new Request)->textFilter((string)$data['repassword']), PASSWORD_DEFAULT);
        $access_token =(new Request)->textFilter((string)$data['access_token']);

        $check_user = DB::getDB()->row('SELECT user_password FROM `users` WHERE user_hid = ?', $access_token);
        if ($check_user['user_password']) {
            //check current password
            if (password_verify((string)$data['oldpassword'], $check_user['user_password'])) {
                if ($data['password'] == $data['repassword']) {
                    DB::getDB()->update('users', [
                        'user_hid' => $password_new, //access_token
                        'user_password' => $password_new //access_token
                    ], [
                        'user_hid' => $access_token
                    ]);
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
                
            }else{
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

    function change_name(){
        $data = json_decode(file_get_contents('php://input'), true);  

        $first_name = ucfirst((new Request)->textFilter((string)$data['first_name']));
        $last_name = ucfirst((new Request)->textFilter((string)$data['last_name']));
        $access_token =(new Request)->textFilter((string)$data['access_token']);

        DB::getDB()->update('users', [
            'user_name' => $first_name,
            'user_lastname' => $last_name
        ], [
            'user_hid' => $access_token
        ]);
        $response = array(
            'status' => Status::OK,
        );
        (new Response)->_e_json($response);

    }

    function change_avatar(){
        $access_token =(new Request)->textFilter((string)$_POST['access_token']);
        $check_user = DB::getDB()->row('SELECT user_id, user_name, user_email, user_lastname, user_group, user_albums_num FROM `users` WHERE user_hid = ?', $access_token);

        if ($check_user['user_id']) {
            //Create user dirs
            $upload_dir = ROOT_DIR . '/public/uploads/users/';
            Filesystem::createDir($upload_dir);
            Filesystem::createDir($upload_dir . $check_user['user_id']);
            Filesystem::createDir($upload_dir . $check_user['user_id'] . '/albums');
            $check_system_albums = DB::getDB()->row('SELECT aid, cover FROM `albums` WHERE user_id = ? AND system = 1', $check_user['user_id']);
        
            if(!$check_system_albums) {
                $hash = md5(md5($server_time).md5($check_user['user_id']).md5($check_user['user_email']));
                $date_create = date('Y-m-d H:i:s', $server_time);
                $sql_privacy = '';
                $this->db->insert('albums', [
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
                $this->db->update('users', [
                    'user_albums_num' => $check_user['user_albums_num']+1,
                ], [
                    'user_id' => $check_user['user_id']
                ]);
            } else {
                $aid_fors = $check_system_albums['aid'];
            }
            $album_dir = ROOT_DIR.'/public/uploads/users/'.$check_user['user_id'].'/albums/'.$aid_fors.'/';
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
                    /**
                     * Photo save to webp
                     * toWebp()
                     */
                    $new_photo_type = '.png';
                    $upload_dir = ROOT_DIR . '/public/uploads/users/' . $check_user['user_id'] . '/';
                    if (move_uploaded_file($image_tmp, $upload_dir . $image_rename . $res_type)) {    
                        $manager = new ImageManager('gd');
                        //Оригинал
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $encoded = $image->toPng(100)->save($upload_dir . 'o_' . $image_rename . $new_photo_type);
                        //Копия 50х50
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $encoded = $image->resize(50, 50)->toPng(70)->save($upload_dir . '50_' . $image_rename . $new_photo_type);
                        //Копия 100х100
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $encoded = $image->resize(100, 100)->toPng(70)->save($upload_dir . '100_' . $image_rename . $new_photo_type);
                        //Главная фотография
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $encoded = $image->toPng(90)->save($upload_dir . $image_rename . $new_photo_type);                        

                        //В альбом
                        $image = $manager->read($upload_dir . $image_rename . $res_type);
                        $encoded = $image->toPng(90)->save($album_dir. $image_rename.$new_photo_type); 

                        $date = date('Y-m-d H:i:s', time());

                        $position_all = $_SESSION['position_all'] ?? null;
                        if($position_all){
                            $position_all = $position_all+1;
                        } else {
                            $position_all = 100000;
                        }

                        $this->db->insert('photos', [
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
                        $ins_id = $this->db->lastInsertId();

                        $check_album = DB::getDB()->row('SELECT photo_num FROM `albums` WHERE aid = ?', $aid_fors);
                        if(!$check_system_albums['cover']){
                            $this->db->update('albums', [
                                'cover' => '',
                                'photo_num' => $check_album['photo_num'] + 1, // note +=
                                'adate' => $date
                            ], [
                                'aid' => $aid_fors
                            ]);
                        }else{    
                            $this->db->update('albums', [
                                'cover' => $image_rename.$new_photo_type,
                                'photo_num' => $check_album['photo_num'] + 1, // note +=
                                'adate' => $date
                            ], [
                                'aid' => $aid_fors
                            ]);                                             
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

                        //Обновляем имя фотки в бд
                        $this->db->update('users', [
                            'user_photo' => $image_rename.$new_photo_type, // note +=
                            // 'user_wall_id' => $dbid
                        ], [
                            'user_id' => $check_user['user_id']
                        ]);  
                        $config = settings_get();
                        $photo =  $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/50_' . $image_rename . $new_photo_type;

                        

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
            (new \Sura\Http\Response)->_e_json($response);  
            exit();
        }

        $response = [
            'status' => $status,
            'photo' => $photo,
        ];
        (new \Sura\Http\Response)->_e_json($response);     
    }    

    function getinfo()
    {
        $config = settings_get();
        $data = json_decode(file_get_contents('php://input'), true);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = DB::getDB()->row('SELECT user_id, user_name, user_lastname, user_photo, user_group FROM `users` WHERE user_hid = ?', $access_token);

        if ($check_user) {
            $check_user['access_token'] = $access_token;
            if ($check_user['user_group'] == 1) {
                $check_user['roles'] = 'ROLE_ADMIN';
            }elseif ($check_user['user_group'] == 5) {
                $check_user['roles'] = 'ROLE_USER';
            }else {
                $check_user['roles'] = 'ROLE_USER';
            }
            if ($check_user['user_photo']) {
                // $photo = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/' . $check_user['user_photo'];
                $photo_50 = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/50_' . $check_user['user_photo'];
                // $photo_100 = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/100_' . $check_user['user_photo'];
            }else{
                // $photo = $config['api_url'] . '/images/no_ava.gif';
                $photo_50 = $config['api_url'] . '/images/no_ava.gif';
                // $photo_100 = $config['api_url'] . '/images/no_ava.gif';
            }            
            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'user_id' => $check_user['user_id'],
                    'access_token' => $check_user['access_token'],
                    'first_name' => $check_user['user_name'],
                    'last_name' => $check_user['user_lastname'],
                    'photo_50' => $photo_50,
                    'roles' => $check_user['roles'],
                ),
            );

            (new Response)->_e_json($response);  

        }else{
            $response = array(
                'status' => Status::NOT_DATA,
            );
    
            (new Response)->_e_json($response);   
        }
  
    }
}