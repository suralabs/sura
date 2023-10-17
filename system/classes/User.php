<?php
/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\classes;

/**
 *
 */
class User
{
    /**
     * @param $id
     * @return array
     */
    public static function getUser($id): array
    {
        return DB::getDB()->row('SELECT user_id, user_real, user_name, user_last_name, user_country_city_name, 
       user_birthday, user_city, user_country, user_photo, user_friends_num, 
       user_notes_num, user_subscriptions_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num,  
       user_privacy, user_sp, user_sex, user_gifts, user_public_num, user_audio, user_delete, 
       user_ban_date, user_logged_mobile, user_rating FROM `users` WHERE user_id =  ?', $id);
    }

    /**
     * @param $name
     * @param $last_name
     * @param $email
     * @param $password
     * @return bool
     */
    public static function addUser($name, $last_name, $email, $password): bool
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $_IP = '0.0.0.0';
        $hid = md5($password);
        $time = time();
        $server_time = time();
        $check_email = \Mozg\classes\DB::getDB()->row(
            'SELECT COUNT(*) AS cnt FROM `users` 
                       WHERE user_email = ?', $email);
        if (!$check_email['cnt']) {
            \Mozg\classes\DB::getDB()->insert('users', [
                'user_last_visit' => $server_time,
                'user_email' => $email,
                'user_password' => $password,
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
                'user_group' => '1',
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
                'user_support' => 0,
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
                'invties_pub_num' => '0',
                'user_real' => '0',
                'user_active' => '0',
                'notify' => '0',
            ]);
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $email
     * @return bool
     */
    public static function removeUser($email): bool
    {
        $check_email = \Mozg\classes\DB::getDB()->row(
            'SELECT COUNT(*) AS cnt FROM `users` 
                       WHERE user_email = ?', $email);
        if ($check_email['cnt']){
            DB::getDB()->delete('users', [
                'user_email' => $check_email['user_email']
            ]);
            return true;
        }else{
            return false;
        }
    }


}