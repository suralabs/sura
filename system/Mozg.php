<?php
/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg;

use Sura\Corner\Error;
use Mozg\exception\ErrorException;
use Sura\Http\Request;
use JsonException;
use Sura\Support\{Registry, Router};
use Mozg\classes\{I18n, DB};

class Mozg
{
    /**
     * @throws ErrorException|JsonException
     */
    public static function initialize(): mixed
    { 
    {
        if (isset($_POST['PHPSESSID'])) {
            \session_id($_POST['PHPSESSID']);
        }

        $lang = I18n::dictionary();
        Registry::set('lang', $lang);

        Registry::set('server_time', \time());
        (new classes\Auth)->login();
        /** @var array $user_info */
        $user_info = Registry::get('user_info');

        /**
         * Если юзер авторизован,
         * то обновляем последнюю дату посещения в таблице друзей и на личной стр
         */
        if (Registry::get('logged')) {
            //Начисления 1 убм.
            if (empty($user_info['user_lastupdate'])) {
                $user_info['user_lastupdate'] = 1;
            }
            $server_time = Registry::get('server_time');
            //Определяем устройство
            $device_user = 0;
            if (empty($user_info['user_last_visit'])) {
                $user_info['user_last_visit'] = $server_time;
            }

            if (($user_info['user_last_visit'] + 60) <= $server_time) {
                if (\date('Y-m-d', $user_info['user_lastupdate']) < \date('Y-m-d', $server_time)) {
                    DB::getDB()->update('users', [
                        'user_logged_mobile' => $device_user,
                        'user_last_visit' => $server_time,
                        'user_balance' => 'user_balance + 1',
                        'user_lastupdate' => $server_time,
                    ], [
                        'user_id' => $user_info['user_id']
                    ]);
                } else {
                    DB::getDB()->update('users', [
                        'user_logged_mobile' => $device_user,
                        'user_last_visit' => $server_time,
                    ], [
                        'user_id' => $user_info['user_id']
                    ]);
                }
            }
        }

        $router = Router::fromGlobals();
        $params = [];
        $routers = [
            '/' => 'Home@main',
            '/api/authorize' => 'Api@authorize',
            '/api/account/register' => 'Api@register',
            '/api/account/getinfo' => 'Api@getinfo',
            '/api/account/restore' => 'Api@restore',
            '/api/account/reset_password' => 'Api@reset_password',
            '/api/account/change_pass' => 'Api@change_pass',
            '/api/account/change_name' => 'Api@change_name',
            '/api/account/change_avatar' => 'Api@change_avatar',
            '/api/users/profile' => 'Profile@profile',
            '/api/albums/all' => 'Albums@all',            
            '/api/search' => 'Search@all',            

            '/api/profile' => 'Profile@api',

        ];
        $router->add($routers);

        if ($router->isFound()) {
            $router->executeHandler($router::getRequestHandler(), $params);
        } else {
            //todo update
            $module = isset($_GET['go']) ?
                htmlspecialchars(strip_tags(stripslashes(trim(urldecode($_GET['go']))))) : 'Home';
            $action = (new Request)->filter('act');
            $class = ucfirst($module);
            if (!class_exists($class) || $action === '' || $class === 'Wall') {

                $text = 'error 404';
                $params = [
                    'title' => $text,
                    'text' => $text,
                ];
                view('info.info', $params);
            } else {
                $controller = new $class();
                $params['params'] = '';
                $params = [$params];
                try {
                    return call_user_func_array([$controller, $action], $params);
                } catch (Error $error) {
                    $params = [
                        'title' => 'error 500',
                        'text' => 'error 500',
                    ];
                    view('info.info', $params);
                }
            }
        }
        return '';//fixme
    }
}
