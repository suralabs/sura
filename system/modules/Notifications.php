<?php

namespace Mozg\modules;

use Mozg\classes\Module;
use Sura\Http\{Request, Response};
use Sura\Support\Status;

use Mozg\classes\{Notify};

class Notifications extends Module
{
  public function get_old()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $page = 1;
    $count = 20;
    $limit_page = ($page - 1) * $count;
    $update_time = time() - 70;
    $check_notify = $this->db->fetchAll('SELECT id, type, date, from_user_id, text, lnk, user_name, user_photo FROM `updates` WHERE for_user_id = ? AND date > ? 
    ORDER by `date` ASC LIMIT '.$limit_page.', '.$count, $check_user['user_id'], $update_time);
    if ($check_notify) {
      $item = array(
        'type'=> $check_notify['0']['type'],
        'date'=> $check_notify['0']['date'],
        'name'=> $check_notify['0']['user_name'],
        'text'=> $check_notify['0']['text'],
        'lnk'=>  $check_notify['0']['lnk'],
        // 'photo'=> $check_notify['user_photo'],
        'from_user'=> $check_notify['0']['from_user_id'],
      );
      // $this->db->query('DELETE FROM updates WHERE id = ?', $check_notify['id']);
    }

    $response = array(
      'status' => Status::OK,
      'data' => array(
        'item' => $item,
      )
    );
    (new Response)->_e_json($response);   
  }

  public function get(){        
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $page = 1;
    $count = 5;
    $limit_page = ($page - 1) * $count;
    $update_time = time() - 70;
    $sql_notify = $this->db->fetchAll('SELECT id, type, date, from_user_id, text, lnk, user_name, user_photo FROM `updates` WHERE for_user_id = ? AND date > ? AND viewed = 0 ORDER by `date` ASC 
    LIMIT '.$limit_page.', '.$count, $check_user['user_id'], $update_time);
    // $this->db->query('DELETE FROM updates WHERE id = ?', $check_notify['id']);

    $count = count($sql_notify);
    $all_notify = array();
    if($sql_notify){                
      foreach ($sql_notify as $key_notify => $notify) {
        $all_notify[$key_notify]['id'] = $notify['id'];
        $all_notify[$key_notify]['date'] = $notify['date'];
        $all_notify[$key_notify]['user_name'] = $notify['user_name'];
        $all_notify[$key_notify]['text'] = $notify['text'];
        $all_notify[$key_notify]['lnk'] = $notify['lnk'];
        $all_notify[$key_notify]['type'] = $notify['type'];

        $this->db->query('UPDATE updates SET', [
           'viewed' => 1
        ], 
        'WHERE id = ?', $sql_notify['id']);
      }
    }
    $response = array(
      'status' => Status::OK,
      'count' => $count,
      'data' => array(
        'items' => $all_notify,
      )
    );
    (new Response)->_e_json($response);   
  }    

  public function addTest(){
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id, user_photo, user_name FROM `users` WHERE user_hid = ?', $access_token);

    if ($check_user) {  
      $for_user_id = $from_user_id = $check_user['user_id'];
      $text = 'Test message';
      $lnk = '';
      (new Notify)->add($for_user_id, $from_user_id, Notify::UPDATE, $text, $lnk);
      $response = array(
        'status' => Status::OK,
      );             
    }else{
      $response = array(
        'status' => Status::BAD,
      );
    }
    (new Response)->_e_json($response);  
  }

  public function all(){        
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $page = 1;
    $count = 5;
    $limit_page = ($page - 1) * $count;
    $sql_notify = $this->db->fetchAll('SELECT id, type, date, from_user_id, text, lnk, user_name, user_photo FROM `updates` WHERE for_user_id = ?  ORDER by `date` ASC  LIMIT '.$limit_page.', '.$count, $check_user['user_id']);
    // $this->db->query('DELETE FROM updates WHERE id = ?', $check_notify['id']);

    $count = count($sql_notify);
    $all_notify = array();
    if($sql_notify){                
      foreach ($sql_notify as $key_notify => $notify) {
        $all_notify[$key_notify]['id'] = $notify['id'];
        $all_notify[$key_notify]['date'] = $notify['date'];
        $all_notify[$key_notify]['user_name'] = $notify['user_name'];
        $all_notify[$key_notify]['text'] = $notify['text'];
        $all_notify[$key_notify]['lnk'] = $notify['lnk'];
        $all_notify[$key_notify]['type'] = $notify['type'];
      }
    }
    $response = array(
      'status' => Status::OK,
      'count' => $count,
      'data' => array(
        'items' => $all_notify,
      )
    );
    (new Response)->_e_json($response);   
  }
}