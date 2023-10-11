<?php

namespace Mozg\modules;

use JetBrains\PhpStorm\NoReturn;
use \Sura\Http\Response;
use \Sura\Http\Request;
use \Sura\Support\Status;
use Mozg\classes\{DB, Module, ViewEmail, Email};
use Intervention\Image\ImageManager;
use Sura\Filesystem\Filesystem;

class Albums  extends Module
{
    /**
     * @throws \JsonException
     */
    final public function all(): void
    {
        $config = settings_get();
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = (new Request)->textFilter((string)$data['id']);
        $access_token = (new Request)->textFilter((string)$data['access_token']);

        $check_albums = DB::getDB()->run("SELECT aid, name, cover, system FROM `albums` WHERE user_id = ? ORDER by `adate` DESC LIMIT 0, 50", $user_id);

        if ($check_albums) {
            $results = array();
            foreach ($check_albums as $key => $item) {
                $results[$key]['id'] = $item['aid'];
                $results[$key]['name'] = $item['name'];
                $upload_dir = $config['api_url'] . 'uploads/users/' . $user_id . '/albums/' . $item['aid'] . '/';
                $results[$key]['cover'] = $upload_dir . $item['cover'];
            }
            
            $response = array(
                'status' => Status::OK,
                'data' => array(
                    'albums' => $results,
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
}