<?php
/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg;

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
    '/' => 'Main@main',
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

    '/api/friends/add' => 'Friends@add',
    '/api/friends/delete' => 'Friends@delete',
    '/api/friends/all' => 'Friends@all',
    '/api/friends/search' => 'Friends@search',
    '/api/friends/requests' => 'Friends@requests',
    '/api/friends/online' => 'Friends@online',
    '/api/friends/common' => 'Friends@common',

    '/api/messages/send' => 'Messages@send',
    '/api/messages/read' => 'Messages@read',
    '/api/messages/typograf' => 'Messages@typograf',
    '/api/messages/delete' => 'Messages@delete',

    '/api/feed/all' => 'Newsfeed@all',

    '/api/notifications/get' => 'Notifications@get',
    '/api/notifications/all' => 'Notifications@all',
    '/api/notifications/test' => 'Notifications@addTest',

    '/api/wall/add' => 'Wall@add',
    '/api/wall/remove' => 'Wall@remove',
    '/api/wall/comment/add' => 'Wall@addComment',
    '/api/wall/comment/remove' => 'Wall@removeComment',
    '/api/wall/like' => 'Wall@like',
    '/api/wall/unlike' => 'Wall@unlike',
    '/api/wall/all' => 'Wall@all',

  ];
  $router->add($routers);

  if ($router->isFound()) {
    $router->executeHandler($router::getRequestHandler(), $params);
  } else {
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
    } catch (ErrorException $error) {
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
