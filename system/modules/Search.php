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
use Mozg\classes\DB;
use \Sura\Http\Response;
use \Sura\Support\Status;

class Search extends Module
{

    /**
     * Search
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function all()
    {
        $config = settings_get();
        
        $data = json_decode(file_get_contents('php://input'), true);
        // $access_token = (new Request)->textFilter((string)$data['access_token']);
        
        $query = (!empty($data['query'])) ? (new Request)->textFilter((string)$data['query']) : null;
        // $query = (new Request)->textFilter((string)$data['query']);
        $page = !empty($data['page']) ? $data['page'] : 1;
        $type = !empty($data['type']) ? $data['type'] : 0;
        $results_count = 20;
        $limit_page = ($page - 1) * $results_count;

        //$where_sql_gen = "WHERE user_delet = '0' AND user_ban = '0'";

        if ($query == null) {
            $sql_query = $this->db->run('SELECT user_id, user_name, user_lastname, user_photo, user_group 
            FROM `users` LIMIT '.$limit_page.', '.$results_count);
        } else {
            $sql_query = $this->db->run('SELECT user_id, user_name, user_lastname, user_photo, user_group 
            FROM users WHERE MATCH (user_name,user_lastname) AGAINST (?) 
            LIMIT '.$limit_page.', '.$results_count, $query);
        }
        
        if ($sql_query) {
            $results = array();
            foreach ($sql_query as $key => $item) {
                $results[$key]['id'] = $item['user_id'];
                $results[$key]['first_name'] = $item['user_name'];
                $results[$key]['last_name'] = $item['user_lastname'];
                if ($item['user_photo']) {
                    $results[$key]['photo'] = $config['api_url'] . 'uploads/users/' . $item['user_id'] . '/' . $item['user_photo'];
                    $results[$key]['photo_50'] = $config['api_url'] . 'uploads/users/' . $item['user_id'] . '/50_' . $item['user_photo'];
                    $results[$key]['photo_100'] = $config['api_url'] . 'uploads/users/' . $item['user_id'] . '/100_' . $item['user_photo'];
                }else{
                    $results[$key]['photo'] = $config['api_url'] . '/images/no_ava.gif';
                    $results[$key]['photo_50'] = $config['api_url'] . '/images/no_ava.gif';
                    $results[$key]['photo_100'] = $config['api_url'] . '/images/no_ava.gif';
                }
            }
            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'results' => $results,
                ),
            );
            (new Response)->_e_json($response); 
        } else {
            $results = array(
                
            );
            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'results' => $results,
                ),
            );
    
            (new Response)->_e_json($response);
        }
    }
}