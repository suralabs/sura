<?php

/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\modules;

use ErrorException;
use Sura\Http\Request;
use Mozg\classes\Cache;
use Mozg\classes\DB;
use Mozg\classes\Module;
use Sura\Http\Response;
use Sura\Support\Registry;
use Mozg\classes\Wall;
use Mozg\classes\WallProfile;
use Sura\Support\Status;

class Profile extends Module
{
    /**
     * @throws \JsonException
     */
    public function api()
    {
        $response = array(
            'status' => Status::NOT_DATA,
        );
        (new Response)->_e_json($response);
    }

    /**
     * @throws \JsonException
     */
    function profile()
    {
        $config = settings_get();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = (new Request)->textFilter((string)$data['id']);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = DB::getDB()->row('SELECT user_id, user_name, user_lastname, user_photo, user_group FROM `users` WHERE user_id = ?', $user_id);

        if ($check_user) {
            // $check_user['access_token'] = $access_token;
            if ($check_user['user_group'] == 1) {
                $check_user['roles'] = 'ROLE_ADMIN';
            }elseif ($check_user['user_group'] == 5) {
                $check_user['roles'] = 'ROLE_USER';
            }else {
                $check_user['roles'] = 'ROLE_USER';
            }
            if ($check_user['user_photo']) {
                $photo = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/' . $check_user['user_photo'];
                $photo_50 = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/50_' . $check_user['user_photo'];
                $photo_100 = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/100_' . $check_user['user_photo'];
            }else{
                $photo = $config['api_url'] . '/images/no_ava.gif';
                $photo_50 = $config['api_url'] . '/images/no_ava.gif';
                $photo_100 = $config['api_url'] . '/images/no_ava.gif';
            }

            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'id' => $check_user['user_id'],
                    // 'access_token' => $check_user['access_token'],
                    'first_name' => $check_user['user_name'],
                    'last_name' => $check_user['user_lastname'],
                    'photo' => $photo,
                    'photo_50' => $photo_50,
                    'photo_100' => $photo_100,
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

    /**
     * @throws \JsonException
     */
    function getInfo(): void
    {
        $config = settings_get();
        $data = json_decode(file_get_contents('php://input'), true);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = DB::getDB()->row('SELECT user_id, user_name, user_lastname, user_photo, user_group FROM `users` WHERE user_hid = ?', $access_token);

        if ($check_user) {
            $check_user['access_token'] = $access_token;
            if ($check_user['user_group'] == 1) {
                $check_user['roles'] = 'ROLE_ADMIN';
            } elseif ($check_user['user_group'] == 5) {
                $check_user['roles'] = 'ROLE_USER';
            } else {
                $check_user['roles'] = 'ROLE_USER';
            }
            if ($check_user['user_photo']) {
                // $photo = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/' . $check_user['user_photo'];
                $photo_50 = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/50_' . $check_user['user_photo'];
                // $photo_100 = $config['api_url'] . 'uploads/users/' . $check_user['user_id'] . '/100_' . $check_user['user_photo'];
            } else {
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

        } else {
            $response = array(
                'status' => Status::NOT_DATA,
            );

            (new Response)->_e_json($response);
        }

    }

}