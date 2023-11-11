<?php

namespace Mozg\classes;

class Notify extends Module
{
    public const UPDATE = 0;
    public const WALL_COMMENT = 1;
    public const PHOTO_COMMENT = 2;
    public const VIDEO_COMMENT = 3;
    public const COMMENT_COMMENT = 4;
    public const GIFT = 5;
    public const MESSAGE = 6;
    public const LIKE = 7;
    public const FRIENDS_YES = 8;
    public const FRIENDS_SEND = 9;

    public function add($for_user, $from_user, $type, $text = '', $lnk = '')
    {
        $row_for_user = $this->db->fetch('SELECT user_last_visit, user_id FROM `users` WHERE user_id = ?', $for_user);
        // $update_time = time() - 70;
        // if ($row_for_user['user_last_visit'] >= $update_time) {
            $row_from_user = $this->db->fetch('SELECT user_sex, user_photo, user_name FROM `users` WHERE user_id = ?', $from_user);

            if($type == Notify::FRIENDS_YES){
                if ($row_from_user['user_sex'] == 2) 
                    {
                        $text = 'подтвердила Вашу заявку на дружбу.';
                    }else{
                        $text = 'подтвердил Вашу заявку на дружбу.';
                    }
                $lnk = '/id' . $row_for_user['user_id'];
            }else if ($type == Notify::FRIENDS_SEND) {
                $text = 'хочет добавить Вас в друзья.';
                $lnk = '/friends/requests';
            }

            $this->db->query('INSERT INTO updates', [
                'for_user_id' => $for_user,
                'from_user_id' => $from_user,
                'type' => $type,
                'date' => time(),
                'text' => $text,
                'user_photo' => '',
                'user_name' => $row_from_user['user_name'],
                'lnk' => $lnk,
            ]); 
            
            return true;
        // }else{
        //     return false;
        // }
    }
}
