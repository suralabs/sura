<?php

namespace Mozg\modules;

use \Sura\Http\Response;
use \Sura\Http\Request;
use \Sura\Support\Status;
use Mozg\classes\{Cache, DB};

class Api  extends \Mozg\classes\Module
{
    final public function main()
    {
        $response = array(
            'status' => '1',
        );

        (new \Sura\Http\Response)->_e_json($response);
    }

    /**
     * authorize
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    final public function authorize()
    {
        var_dump($_POST);
        exit;

        $email = (new Request)->filter('email');
        $password = md5(md5(stripslashes((new Request)->filter('password'))));
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
}