<?php

namespace Mozg\modules;

use Mozg\classes\Module;
use Sura\Http\{Request, Response};
use Sura\Support\Status;

class Newsfeed extends Module
{
  /**
   * @throws \JsonException
   */
  final public function all(): void
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $page = $data['page'] ?? 1;
    $page_count = 20;
    $limit_page = ((int)$page - 1) * $page_count;
    $access_token = (string)$data['access_token'];
    $check_user = $this->db->fetch('SELECT user_id, user_photo FROM `users` WHERE user_hid = ?', $access_token);
    $config = settings_get();

    $sql_ = $this->db->fetchAll('SELECT tb1.id, author, content, add_date, type
    FROM `wall` tb1
    WHERE tb1.author IN (SELECT tb2.friend_id FROM `friends` tb2 
    WHERE user_id = ? AND tb1.type IN (1,2,3)) 
    OR 
      tb1.author IN (SELECT tb2.friend_id FROM `friends` tb2 
      WHERE user_id = ? AND tb1.type = 11 AND subscriptions = 2) 
    AND tb1.type IN (1,2,3,11) 
    ORDER BY tb1.add_date DESC LIMIT ?, ?', $check_user['user_id'], $check_user['user_id'], $page_count, $limit_page);

    $items = array();
    foreach ($sql_ as $key => $item) {
      if ($item['type'] == 2) {
        //Если видео
        $author_info = $this->db->fetch('SELECT user_search_pref, user_last_update, user_photo, user_sex, user_privacy FROM `users` WHERE user_id = ?', $item['author']);
        if ($author_info['user_photo']){
          $items[$key]['ava'] = '/uploads/users/' . $item['author'] . '/50_' . $author_info['user_photo'];
        }else{
          $items[$key]['ava'] = '/images/no_ava_50.png';
        }
        $items[$key]['online'] = \Mozg\Models\Users::checkOnline($author_info['user_last_update']);

        $content = unserialize($item['content']);
        foreach ($content as $key_content => $item_content) {
          if (file_exists(ROOT_DIR . $item_content['file'])) {
            $items[$key]['attach'][$key_content]['name'] = $item_content['name'];
            $items[$key]['attach'][$key_content]['file'] = $item_content['file'];
          }
        }                    
      }else if ($item['type'] == 3) {
        //Если фотография
        $author_info = $this->db->fetch('SELECT user_search_pref, user_last_update, user_photo, user_sex, user_privacy FROM `users` WHERE user_id = ?', $item['author']);
        if ($author_info['user_photo']){
          $items[$key]['ava'] = '/uploads/users/' . $item['author'] . '/50_' . $author_info['user_photo'];
        }else{
          $items[$key]['ava'] = '/images/no_ava_50.png';
        }
        $items[$key]['online'] = \Mozg\Models\Users::checkOnline($author_info['user_last_update']);

        $content = unserialize($item['content']);
        foreach ($content as $key_content => $item_content) {
          if (file_exists(ROOT_DIR . $item_content['file'])) {
            $items[$key]['attach'][$key_content]['name'] = $item_content['name'];
            $items[$key]['attach'][$key_content]['file'] = $item_content['file'];
          }
        }                  
      }else if ($item['type'] == 1) {
        //Если запись со стены
        //Приватность
        $author_info = $this->db->fetch('SELECT user_search_pref, user_last_update, user_photo, user_sex, user_privacy FROM `users` WHERE user_id = ?', $item['author']);
        if ($author_info['user_photo']){
          $items[$key]['ava'] = '/uploads/users/' . $item['author'] . '/50_' . $author_info['user_photo'];
        }else{
          $items[$key]['ava'] = '/images/no_ava_50.png';
        }
        $items[$key]['online'] = \Mozg\Models\Users::checkOnline($author_info['user_last_update']);

        //Выводим кол-во мне нравится
        $wall_likes = $this->db->fetchAll("SELECT user_id, date 
        FROM `wall_like` WHERE wall = '{$item['id']}'");
        
        //Выводим кол-во комментов
        if ($item['comments_num'] > 3)
          $comments_limit = $item['comments_num'] - 3;
        else
          $comments_limit = 0;

        $sql_comments = $this->db->fetchAll('SELECT tb1.id, author, content, add_date, attach, type, tb2.user_photo, user_name, user_last_update
        FROM `wall_comments` tb1, `users` tb2 
        WHERE tb1.author = tb2.user_id AND tb1.wall_id = ? ORDER by `add_date` ASC LIMIT ?, 3', $item['id'], $comments_limit);
        $items_comments = array();
        foreach ($sql_comments as $key_commet => $row_comments) {
          $items_comments[$key_commet]['name'] = $row_comments['user_name'];
          if ($row_comments['user_photo']){
            $items_comments[$key_commet]['ava'] = $config['home_url'] . 'uploads/users/' . $row_comments['author_user_id'] . '/50_' . $row_comments['user_photo'];
          }else{
            $items_comments[$key_commet]['ava'] = '/images/no_ava_50.png';
          }
          $items_comments[$key_commet]['online'] = \Mozg\Models\Users::checkOnline($row_comments['user_last_update']);
        }

      }else if ($item['type'] == 11) {
        //Если запись со стены сообщества
        //Приватность

        $author_info = $this->db->fetch('SELECT title, photo, comments FROM `communities` WHERE id = ?', $item['author']);
        //group
        if ($author_info['photo']){
          $items[$key]['ava'] = '/uploads/groups/' . $item['author'] . '/50_' . $author_info['photo'];
        }
        else{
          $items[$key]['ava'] = '/images/no_ava_50.png';
        }

        //Выводим кол-во мне нравится
        $wall_likes = $this->db->fetch('SELECT user_id, date FROM `wall_like` WHERE wall = ?', $item['id']);
        
        //Выводим кол-во комментов
        if ($item['comments'] > 3)
          $comments_limit = $item['comments'] - 3;
        else
          $comments_limit = 0;

        $sql_comments = $this->db->fetchAll('SELECT tb1.id, author, content, add_date, attach, type, tb2.user_photo, user_name, user_last_update
        FROM `wall_comments` tb1, `users` tb2 
        WHERE tb1.author = tb2.user_id AND tb1.wall_id = ? ORDER by `add_date` ASC LIMIT ?, 3"', $item['id'], $comments_limit);
        $items_comments = array();
        foreach ($sql_comments as $key_commet => $row_comments) {
          $items_comments[$key_commet]['name'] = $row_comments['user_name'];
          if ($row_comments['user_photo']){
            $items_comments[$key_commet]['ava'] = $config['home_url'] . 'uploads/users/' . $row_comments['author_user_id'] . '/50_' . $row_comments['user_photo'];
          }else{
            $items_comments[$key_commet]['ava'] = '/images/no_ava_50.png';
          }
          $items_comments[$key_commet]['online'] = \Mozg\Models\Users::checkOnline($row_comments['user_last_update']);
        }
      }            
    }
    
    if ($check_user['user_photo']){
      $ava = '/uploads/users/' . $check_user['user_id'] . '/50_' . $check_user['user_photo'];
    }else{
      $ava = '/images/no_ava_50.png';
    }

    $response = array(
      'status' => '1',
      'data' => array(
        'walls'=> $items,
      )
    );

    (new Response)->_e_json($response);
  }

}