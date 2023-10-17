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
        $lang = I18n::dictionary();
        Registry::set('lang', $lang);

        Registry::set('server_time', \time());

        $router = Router::fromGlobals();
        $params = [];
        $routers = [
            '/' => 'Home@main',
            '/api/authorize' => 'Auth@authorize',
            '/api/account/register' => 'Auth@register',
            '/api/account/getinfo' => 'Profile@getInfo',
            '/api/account/restore' => 'Auth@restore',
            '/api/account/reset_password' => 'Auth@reset_password',
            '/api/account/change_pass' => 'Settings@change_pass',
            '/api/account/change_name' => 'Settings@change_name',
            '/api/account/change_avatar' => 'Settings@change_avatar',
            '/api/account/change_bio' => 'Profile@bioEdit',

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
        return true;
    }
}
