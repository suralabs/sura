<?php

/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\modules;

use Sura\Http\Request;
use Mozg\classes\Module;
use Sura\Http\Response;
use Sura\Support\Status;

class Wall extends Module
{
  /**
   * 
   */
  public function add()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $user_id = $data['user_id'];
    $content = (new Request)->textFilter((string)$data['content']);
    $type = $data['type'];
    $add_time = time();

    if (!empty($content)) {
      $owner = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
      $check_user = $this->db->fetch('SELECT user_id, user_wall_num FROM `users` WHERE user_id = ?', $user_id);
      //todo privacy
      if($type == 1 || $type == 2){
        if ($owner &&  $check_user) {
          $this->db->query('INSERT INTO wall', [
            'author' => $owner['user_id'],//автор
            'for_user_id' => $check_user['user_id'],//кому
            'type' => $type,//profile/group
            'content' => $content,
            'add_date' => $add_time,
            'attach' => '',//photos //todo            
            'privacy' => '',//privacy //todo
            'likes_num' => '0',
            'comments_num' => '0',
            'tell_uid' => '0',
            'tell_date' => '0',
            'tell_id' => '0',
          ]);
          $this->db->query('UPDATE users SET', [
            'user_wall_num+=' => 1, // note +=
          ], 'WHERE user_id = ?', $check_user['user_id']);

          $response = array(
            'status' => Status::OK,
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
          'status' => Status::BAD,
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
  
  /**
   * 
   */
  public function remove()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $wall_id = $data['wall_id'];

    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    if ($check_user) {
      $check_wall = $this->db->fetch('SELECT id, author, for_user_id FROM `wall` WHERE id = ?', $wall_id);
      //
      if ($check_wall['author'] == $check_user['user_id'] || $check_wall['for_user_id'] == $check_user['user_id']) {
        $this->db->query('DELETE FROM wall WHERE id = ?', $check_wall['id']);
        $response = array(
          'status' => Status::OK,
        );
        (new Response)->_e_json($response);                
      }else{
        $response = array(
          'status' => Status::NOT_FOUND,
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

  /**
   * 
   */
  public function addComment()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $wall_id = (new Request)->textFilter((string)$data['wall_id']);
    $content = (new Request)->textFilter((string)$data['content']);

    $owner = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    if ($owner) {
      $check_wall = $this->db->fetch('SELECT id, author, comments_num FROM `wall` WHERE id = ?', $wall_id);
      if ($check_wall) {
        //$check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_id = ?', $check_wall['author']);
        //todo privacy
        $this->db->query('INSERT INTO wall_comments', [
          'wall_id' => $wall_id,
          'author' => $owner['user_id'],
          'content' => $content,
          'add_date' => time(),
          'attach' => '',
          'type' => '1',  
        ]);

        //todo upd num
        $response = array(
          'status' => Status::OK,
        );
        (new Response)->_e_json($response); 
      }else{
        $response = array(
          'status' => Status::NOT_FOUND,
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

  /**
   * 
   */
  public function removeComment()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $comment_id = (new Request)->textFilter((string)$data['comment_id']);

    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    if ($check_user) {
      $check_wall_comments = $this->db->fetch('SELECT id FROM `wall_comments` WHERE author = ? AND id = ', $check_user['user_id'], $comment_id);
      if ($check_wall_comments) {
        $this->db->query('DELETE FROM wall_comments WHERE id = ? AND author = ?', $check_wall_comments['id'], $check_user['user_id']);
        $response = array(
          'status' => Status::OK,
        );
        (new Response)->_e_json($response);                
      }else{
        $response = array(
          'status' => Status::NOT_FOUND,
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

  /**
   * 
   */
  public function like()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $wall_id = (new Request)->textFilter((string)$data['wall_id']);

    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    if ($check_user) {
      $check_wall = $this->db->fetch('SELECT id, author, likes_num FROM `wall` WHERE id = ?', $wall_id);
      $check_like = $this->db->fetch('SELECT id FROM `wall_like` WHERE wall = ? AND user_id', $wall_id, $check_user['user_id']);
      if (!$check_like) {
        $this->db->query('INSERT INTO wall_like', [
          'wall' => $check_wall['id'],       
          'user_id' => $check_user['user_id'],       
          'date' => time(),  
        ]);
        $response = array(
          'status' => Status::OK,
        );
        (new Response)->_e_json($response);                 
      } else {
        $response = array(
          'status' => Status::BAD,
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

  /**
   * 
   */
  public function unlike()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $wall_id = (new Request)->textFilter((string)$data['wall_id']);

    $check_user = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    if ($check_user) {
      $check_wall = $this->db->fetch('SELECT id, author, likes_num FROM `wall` WHERE id = ?', $wall_id);
      $check_like = $this->db->fetch('SELECT id FROM `wall_like` WHERE wall = ? AND user_id', $wall_id, $check_user['user_id']);
      if ($check_like) {
        $this->db->query('DELETE FROM wall_like WHERE id = ? AND user_id = ?', $check_wall['id'], $check_user['user_id']);

        $response = array(
          'status' => Status::OK,
        );
        (new Response)->_e_json($response);                 
      } else {
        $response = array(
          'status' => Status::BAD,
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

  /**
   * 
   */
  public function all()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $access_token = (new Request)->textFilter((string)$data['access_token']);
    $user_id = (new Request)->textFilter((string)$data['user_id']);

    $page = !empty($data['page']) ? $data['page'] : 1;
    $results_count = 20;
    $limit_page = ($page - 1) * $results_count;

    $owner = $this->db->fetch('SELECT user_id FROM `users` WHERE user_hid = ?', $access_token);
    $check_user = $this->db->fetch('SELECT user_id, user_wall_num FROM `users` WHERE user_id = ?', $user_id);
    //todo privacy
    
    $sql_query = $this->db->fetchAll('SELECT * FROM `wall` WHERE for_user_id = ? 
    LIMIT '.$limit_page.', '.$results_count, $check_user['user_id']);

    if ($sql_query) {
      $results = array();
      foreach ($sql_query as $key => $item) {
        $results[$key]['id'] = $item['id'];
        $results[$key]['author_id'] = $item['author'];
        $results[$key]['for_user_id'] = $item['for_user_id'];
        $results[$key]['type'] = $item['type'];
        $results[$key]['content'] = $item['content'];
        $results[$key]['add_date'] = $item['add_date'];
        // $results[$key]['attach'] = $item['attach'];
        $results[$key]['tell_uid'] = $item['tell_uid'];
        $results[$key]['tell_date'] = $item['tell_date'];
        $results[$key]['tell_id'] = $item['tell_id'];
      }
      $response = array(
        'status' => Status::OK,
        'data' => array(
          'results' => $results,
        ),
      );
      (new Response)->_e_json($response); 
    } else {
      $response = array(
        'status' => Status::BAD,
      );
      (new Response)->_e_json($response);   
    }        
  }  
}