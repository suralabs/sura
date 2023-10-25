<?php

namespace Mozg\classes;

class Notify extends Module
{
    public function add($for_user, $from_user, $type, $lnk = '', $text = '')
    {
        $row_for_user = $this->db->fetch('SELECT user_last_visit, user_id FROM `users` WHERE user_id = ?', $for_user);
        $update_time = time() - 70;
        if ($row_for_user['user_last_visit'] >= $update_time) {
            $row_from_user = $this->db->fetch('SELECT user_sex, user_photo, user_name FROM `users` WHERE user_id = ?', $from_user);

            if($type == 12){
                if ($row_from_user['user_sex'] == 2) 
                    $text = 'подтвердила Вашу заявку на дружбу.';
                else 
                    $text = 'подтвердил Вашу заявку на дружбу.';
                $lnk = '/id' . $row_for_user['user_id'];
            }elseif ($type == 11) {
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
        }
    }
}
