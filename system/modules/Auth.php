<?php

namespace Mozg\modules;

use Mozg\classes\DB;
use Mozg\classes\Email;
use Mozg\classes\Module;
use Mozg\classes\ViewEmail;
use Sura\Http\Request;
use Sura\Http\Response;
use Sura\Support\Status;

class Auth  extends Module
{

    /**
     * authorize
     *
     *
     * @throws \JsonException
     */
    final public function authorize(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = (new Request)->textFilter((string)$data['email']);
        $password = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::NOT_VALID,
                'data' => $email . ' ' . $password,
            );
            (new Response)->_e_json($response);
            exit();
        }
        if (!empty($email)) {
            $check_user = $this->db->fetch('SELECT user_id, user_password, user_hid FROM `users` WHERE user_email = ?', $email);
            if ($check_user) {
                if (password_verify($data['password'], $check_user['user_password'])) {
                    $hid = $password;
                    $hid = $check_user['user_hid'];
                    // $this->db->query('UPDATE users SET', [
                    //     'user_hid' => $hid
                    // ], 
                    // 'WHERE user_id = ?', $check_user['user_id']);

                    $this->db->query('DELETE FROM updates WHERE for_user_id = ?', $check_user['user_id']);

                    $response = array(
                        'status' => Status::OK,
                        'access_token' => $hid,
                    );
                    (new Response)->_e_json($response);
                } else {
                    $response = array(
                        'status' => Status::BAD_PASSWORD,
                    );
                    (new Response)->_e_json($response);
                }
            } else {
                $response = array(
                    'status' => Status::NOT_USER,
                );
                (new Response)->_e_json($response);
            }
        } else {
            $response = array(
                'status' => Status::NOT_DATA,
            );

            (new Response)->_e_json($response);
        }
    }

    /**
     * @throws \JsonException
     */
    function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = (new Request)->textFilter((string)$data['first_name']);
        $last_name = (new Request)->textFilter((string)$data['last_name']);
        $email = (new Request)->textFilter((string)$data['email']);
        $pass = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        $repass = password_hash((new Request)->textFilter((string)$data['re_password']), PASSWORD_DEFAULT);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::BAD_MAIL,
            );
            (new Response)->_e_json($response);
            exit();
        }

        if (!password_verify($data['password'], $repass)) {
            $response = array(
                'status' => Status::BAD_PASSWORD,//5
            );
            (new Response)->_e_json($response);
            exit();
        }

        $_IP = '0.0.0.0';
        $hid = $repass;
        $time = time();
        $server_time = time();
        $check_email = $this->db->fetch('SELECT COUNT(*) AS cnt FROM `users` WHERE user_email = ?', $email);
        if (!$check_email['cnt']) {
            $this->db->query('INSERT INTO users', [
                'user_last_visit' => $server_time,
                'user_email' => $email,
                'user_password' => $pass,
                'user_name' => $name,
                'user_last_name' => $last_name,
                'user_photo' => '',
                'user_day' => '0',
                'user_month' => '0',
                'user_year' => '0',
                'user_country' => '0',
                'user_city' => '0',
                'user_reg_date' => $server_time,
                'user_last_date' => $server_time,
                'user_group' => '5',
                'user_hid' => $hid,
                'user_birthday' => '0-0-0',
                'user_privacy' => 'val_msg|1||val_wall1|1||val_wall2|1||val_wall3|1||val_info|1||',
                'user_wall_id' => '0',
                'user_sex' => '0',
                'user_country_city_name' => '',
                'user_albums_num' => '0',
                'user_friends_demands' => '0',
                'user_friends_num' => '0',
                'user_fave_num' => '0',
                'user_pm_num' => '0',
                'user_notes_num' => '0',
                'user_subscriptions_num' => '0',
                'user_videos_num' => '0',
                'user_wall_num' => '0',
                'user_blacklist_num' => '0',
                'user_blacklist' => '0',
                'user_sp' => '',
                'user_support' => '0',
                'user_balance' => '0',
                'user_last_update' => $server_time,
                'user_gifts' => '0',
                'user_public_num' => '0',
                'user_audio' => '0',
                'user_delete' => '0',
                'user_ban' => '0',
                'user_ban_date' => '0',
                'user_new_mark_photos' => 0,
                'user_doc_num' => '0',
                'user_logged_mobile' => '0',
                'balance_rub' => '0',
                'user_rating' => '0',
                'invite_pub_num' => '0',
                'user_real' => '0',
                'user_active' => '0',
                'notify' => '0',
                'user_bio' => '',
            ]);

            $response = array(
                'status' => Status::OK,
                'access_token' => $hid,
            );

            (new Response)->_e_json($response);
        } else {
            $response = array(
                'status' => Status::NOT_DATA,//20
                'data' => $check_email['cnt'],
            );

            (new Response)->_e_json($response);
        }
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    function restore(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = (new Request)->textFilter((string)$data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array(
                'status' => Status::BAD_MAIL,
            );
            (new Response)->_e_json($response);
            exit();
        }
        $check = $this->db->fetch('SELECT user_id, user_photo, user_name FROM `users` WHERE user_email = ?', $email);
        if ($check) {
            //Удаляем все предыдущие запросы на восстановление
            $this->db->query('DELETE FROM restore WHERE email = ?', $email);
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
            $this->db->query('INSERT INTO restore', [
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
            /** @var ?string $dictionary ['lost_subj'] */
            Email::send($email, $dictionary['lost_subj'], $message);

            $response = array(
                'status' => Status::OK,
            );
        } else {
            $response = array(
                'status' => Status::NOT_USER,
            );
        }
        (new Response)->_e_json($response);
    }

    /**
     * @throws \JsonException
     */
    function reset_password(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $pass = password_hash((new Request)->textFilter((string)$data['password']), PASSWORD_DEFAULT);
        $repass = password_hash((new Request)->textFilter((string)$data['re_password']), PASSWORD_DEFAULT);
        $hash = (new Request)->textFilter((string)$data['hash']);
        if (strlen($data['password']) >= 6 and $data['password'] === $data['re_password']) {
            $row = $this->db->fetch('SELECT email FROM `restore` WHERE hash = ? ', $hash);
            if ($row['email']) {
                $this->db->query('UPDATE users SET', [
                    'user_password' => $pass,
                    'user_hid' => $repass,
                ], 'WHERE user_email = ?', $row['email']);

                $this->db->query('DELETE FROM restore WHERE email = ?', $row['email']);

                $response = array(
                    'status' => Status::OK,
                    'access_token' => $repass,
                );
            } else {
                $response = array(
                    'status' => Status::NOT_VALID,
                );
            }
            (new Response)->_e_json($response);
        } else {
            $response = array(
                'status' => Status::BAD_PASSWORD,
            );
            (new Response)->_e_json($response);
        }
    }

}