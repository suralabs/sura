<?php

/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\modules;

use Mozg\classes\Friendship;
use Sura\Http\Request;
use Mozg\classes\Module;
use Sura\Http\Response;
use Sura\Support\Status;

class Profile extends Module
{
    /**
     * @throws \JsonException
     */
    function profile()
    {
        $config = settings_get();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = (new Request)->textFilter((string)$data['id']);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = $this->db->fetch('SELECT user_id, user_name, user_last_name, user_photo, user_group, user_hid, user_bio, user_friends_num, user_albums_num, user_wall_num FROM `users` WHERE user_id = ?', $user_id);
        $my_info = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);

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

            $owner = ($check_user['user_id'] == $my_info['user_id']) ?? false;
            //checks
            if($check_user['user_id'] !== $my_info['user_id']){
                $check_friends = (new Friendship($my_info['user_id']))->checkFriends($check_user['user_id']);

                $my_check_subscription = $this->db->fetch('SELECT user_id FROM `friends` WHERE user_id = ? AND friend_id = ? AND subscriptions = 1', $my_info['user_id'], $check_user['user_id']);
                $check_subscription = $this->db->fetch('SELECT user_id FROM `friends` WHERE user_id = ? AND friend_id = ? AND subscriptions = 1', $check_user['user_id'], $my_info['user_id'] );
                if($check_friends == false && $my_check_subscription == false && $check_subscription == false){
                    $friend_status = 0;
                }elseif($check_friends == false && $my_check_subscription == true){
                    $friend_status = 1;
                }elseif($check_friends == false && $check_subscription == true){
                    $friend_status = 2;
                }elseif($check_friends == true && $my_check_subscription == true){
                    $friend_status = 3;
                }


            }else{
                $check_friends = null;
                $check_subscription = null;
                $friend_status = null;
            }

            //friends
            $all_friends = array();
            if ($check_user['user_friends_num']) {
                $sql_friends = $this->db->fetchAll("SELECT tb1.friend_id, tb2.user_name, user_last_name, user_photo FROM `friends` tb1, `users` tb2 
                WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", true);
                if($sql_friends){                
                    foreach ($sql_friends as $key_friend => $friend) {
                        $all_friends[$key_friend]['user_id'] = $friend['friend_id'];
                        $all_friends[$key_friend]['name'] = $friend['user_name'];
                        $all_friends[$key_friend]['last_name'] = $friend['user_last_name'];                    
                        if ($friend['user_photo']) {
                            $all_friends[$key_friend]['ava'] = $config['home_url'] . 'uploads/users/' . $friend['friend_id'] . '/50_' . $friend['user_photo'];
                        } else {
                            $all_friends[$key_friend]['ava'] = '/images/no_ava_50.png';
                        }
                    }
                }
            }

            $all_albums = array();
            if($check_user['user_albums_num']){
                //################### Альбомы ###################//
                if ($check_user['user_id'] !== $my_info['user_id']) {
                    $albums_privacy = false;
                    $cache_pref = '';
                } elseif ($check_friends) {
                    $albums_privacy = "AND SUBSTRING(privacy, 1, 1) regexp '[[:<:]](1|2)[[:>:]]'";

                    $cache_pref = "_friends";
                } else {
                    $albums_privacy = "AND SUBSTRING(privacy, 1, 1) = 1";
                    $cache_pref = "_all";
                }

                $sql_albums = $this->db->fetchAll('SELECT aid, name, adate, photo_num, cover FROM `albums` WHERE user_id = ? ' . $albums_privacy . ' ORDER by `position` ASC LIMIT 0, 4', $check_user['user_id']);

                if ($sql_albums) {
                    foreach ($sql_albums as $key_album => $album) {
                        $all_albums[$key_album]['name'] = stripslashes($album['name']);
                        $all_albums[$key_album]['date'] = megaDate((int)$album['adate']);
                        $all_albums[$key_album]['albums_photos_num'] = declWord($album['photo_num'], 'photos');
                        if ($album['cover']) {
                            $all_albums[$key_album]['album_cover'] = "/uploads/users/{$check_user['user_id']}/albums/{$album['aid']}/c_{$album['cover']}";
                        } else {
                            $all_albums[$key_album]['album_cover'] = '/images/no_cover.png';
                        }
                    }
                }
            }

            $all_walls = array();
            if($check_user['user_wall_num']){

                $page = 1;
                $results_count = 10;
                $limit_page = ($page - 1) * $results_count;
                
                $sql_wall = $this->db->fetchAll('SELECT tb1.id, author_user_id, text, add_date, 
                fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, 
                tell_comm, tb2.user_photo, user_name, user_last_name, user_last_visit 
                FROM `wall` tb1, `users` tb2 
                WHERE for_user_id = ? AND tb1.author_user_id = tb2.user_id 
                  AND tb1.fast_comm_id = 0   
                ORDER by `add_date` DESC LIMIT ' . $limit_page . ' , ' . $results_count, $check_user['user_id']);
                if($sql_wall){
                    foreach ($sql_wall as $key_wall => $wall) {
                        $all_walls[$key_wall]['id'] = $wall['id'];
                        $all_walls[$key_wall]['author_user_id'] = $wall['author_user_id'];
                        $all_walls[$key_wall]['text'] = $wall['text'];
                        $all_walls[$key_wall]['add_date'] = $wall['add_date'];
                        $all_walls[$key_wall]['fasts_num'] = $wall['fasts_num'];
                        $all_walls[$key_wall]['likes_num'] = $wall['likes_num'];
                        $all_walls[$key_wall]['likes_users'] = $wall['likes_users'];
                        $all_walls[$key_wall]['tell_uid'] = $wall['tell_uid'];
                        $all_walls[$key_wall]['type'] = $wall['type'];
                        $all_walls[$key_wall]['tell_date'] = $wall['tell_date'];
                        $all_walls[$key_wall]['public'] = $wall['public'];
                        $all_walls[$key_wall]['attach'] = $wall['attach'];
                        $all_walls[$key_wall]['tell_comm'] = $wall['tell_comm'];
                        if ($wall['user_photo']) {
                            $all_walls[$key_wall]['user_photo'] = $config['home_url'] . 'uploads/users/' . $wall['author_user_id'] . '/50_' . $wall['user_photo'];
                        } else {
                            $all_walls[$key_wall]['user_photo'] = '/images/no_ava_50.png';
                        }
                        $all_walls[$key_wall]['user_photo'] = '/images/no_cover.png';
                        $all_walls[$key_wall]['user_name'] = $wall['user_name'];
                        $all_walls[$key_wall]['user_last_name'] = $wall['user_last_name'];
                        $all_walls[$key_wall]['user_last_visit'] = $wall['user_last_visit'];
                    }
                }
            }

            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'id' => $check_user['user_id'],
                    'first_name' => $check_user['user_name'],
                    'last_name' => $check_user['user_last_name'],
                    'photo' => $photo,
                    'photo_50' => $photo_50,
                    'photo_100' => $photo_100,
                    'roles' => $check_user['roles'],
                    'bio' => $check_user['user_bio'],
                    'owner' => $owner,
                    'friends' => $all_friends,
                    'albums' => $all_albums,
                    'walls' => $all_walls,
                    'friend_status' => $friend_status,
                    'counters' => array(
                        'friends' => $check_user['user_friends_num'],
                        'albums' => $check_user['user_albums_num'],
                        'user_wall_num' => $check_user['user_wall_num'],
                    ),
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
        $check_user = $this->db->fetch('SELECT user_id, user_name, user_last_name, user_photo, user_group FROM `users` WHERE user_hid = ?', $access_token);

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
                    'last_name' => $check_user['user_last_name'],
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

    function bioEdit()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $bio = (new Request)->textFilter((string)$data['bio']);
        $check_user = $this->db->fetch('SELECT user_id, user_bio FROM `users` WHERE user_hid = ?', $access_token);
        if ($check_user) {
            $this->db->query('UPDATE users SET', [
                'user_bio' => $bio,
            ], 'WHERE user_hid = ?', $access_token);

            $response = array(
                'status' => Status::OK,
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