<?php

namespace Mozg\modules;

use Mozg\classes\Module;
use Sura\Http\{Request, Response};
use Sura\Support\Status;

class Notifications extends Module
{
    public function get()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $access_token = (new Request)->textFilter((string)$data['access_token']);
        $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
        $update_time = time() - 70;
        $check_notify = $this->db->fetch('SELECT id, type, date, from_user_id, text, lnk, user_name, user_photo FROM `updates` WHERE for_user_id = ? AND date > ?  ORDER by `date` ASC', $check_user['user_id'] , $update_time);
        if ($check_notify) {
            $item = array(
                'type'=> $check_notify['type'],
                'date'=> $check_notify['date'],
                'name'=> $check_notify['user_name'],
                'text'=> $check_notify['text'],
                'lnk'=> $check_notify['lnk'],
                // 'photo'=> $check_notify['user_photo'],
                'from_user'=> $check_notify['from_user_id'],
            );
            $this->db->query('DELETE FROM updates WHERE id = ?', $check_notify['id']);
        }

        $response = array(
            'status' => Status::OK,
            'data' => array(
                'item' => $check_notify['type'],
            )
        );
        (new Response)->_e_json($response);   
    }

    public function all(){
        
    }
}