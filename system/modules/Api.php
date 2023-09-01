<?php

namespace Mozg\modules;

use JetBrains\PhpStorm\NoReturn;
use \Sura\Http\Response;
use \Sura\Http\Request;
use \Sura\Support\Status;
use Mozg\classes\{DB, Module};

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
    #[NoReturn] final public function authorize()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = (new Request)->textFilter($data["email"]);
        $password = md5(md5(stripslashes((new Request)->textFilter($data["password"]))));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::NOT_VALID,
                'data' => $email. ' ' . $password,
            );
            (new Response)->_e_json($response);
            exit();
        }
        if (!empty($email)) {
            $check_user = DB::getDB()->row('SELECT user_id FROM `users` WHERE user_email = ? AND user_password = ?', $email, $password);
            if ($check_user) {
                $hid = $password . md5($password);
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
        
        $name = (new Request)->textFilter($data['firstname']);
        $lastname = (new Request)->textFilter($data['lastname']);
        $email = (new Request)->textFilter($data['email']);
        $pass = password_hash((new Request)->textFilter($data['password']), PASSWORD_DEFAULT);
        $repass = password_hash((new Request)->textFilter($data['repassword']), PASSWORD_DEFAULT);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::NOT_VALID,
                // 'data' => $email. ' ' . $password,
            );
            (new Response)->_e_json($response);
            exit();
        }
        if($pass !== $repass){
            $response = array(
                'status' => Status::NOT_VALID,
                // 'data' => $email. ' ' . $password,
            );
            (new Response)->_e_json($response);
            exit();
        }
        
        $_IP = '0.0.0.0';
        $hid = md5($pass);
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
                'user_support' => 0,
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
                'status' => Status::NOT_DATA,
            );
    
            (new Response)->_e_json($response);   
            exit();
        }

        $response = array(
            'status' => Status::NOT_DATA,
        );

        (new Response)->_e_json($response);   
    }
}