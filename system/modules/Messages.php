<?php

namespace Mozg\modules;

use Mozg\classes\{Module, Dialog};
use Sura\Http\{Request, Response};
use Sura\Support\Status;

class Messages extends Module
{

  public function send()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $from_user_id = $check_user['user_id'];
    $for_user_id = (int)$data['user'];        
    $room_id = $data['room_id'];        
    $msg = $data['msg'];        
    $attach_files = $data['attach_files'];        

    (new Dialog($from_user_id))->send($for_user_id, $room_id, $msg, $attach_files);
  }

  public function read()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $from_user_id = $check_user['user_id'];
    $msg_id = $data['msg_id']; 
    
    (new Dialog($from_user_id))->read($msg_id);
  }

  public function typograf()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $from_user_id = $check_user['user_id'];
    $for_user_id = (int)$data['user'];  

    $room_id = $data['room_id']; 
    $action = $data['action']; 

    (new Dialog($from_user_id))->typograf($room_id, $for_user_id, $action);
  }

  public function delete()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $from_user_id = $check_user['user_id'];

    $room_id = $data['room_id'];
    $im_user_id = $data['im_user_id'];
    $room_id = $data['room_id'];

    (new Dialog($from_user_id))->delete($room_id, $im_user_id);
  }


}