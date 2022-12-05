<?php
/*
 * Copyright (c) 2022 Tephida
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\modules;

class Admin
{
    public function main()
    {
        $params['title'] = 'title';
        $params['mods'] = [
            'Настройка системы' => array(
                'description' => 'Настройка общих параметров скрипта, а также настройка системы безопасности скрипта',
                'link' => 'system',
                'icon' => 'settings',
            ),
            'Инструменты' => array(
                'description' => 'Настройка общих параметров скрипта, а также настройка системы безопасности скрипта',
                'link' => 'tools',
                'icon' => 'settings',
            ),
//    'Личные настройки' => array(
//        'description' => 'Управление и настройка вашего личного профиля пользователя.',
//        'link' => 'mysettings',
//        'icon' => 'mysettings',
//    ),
            'Пользователи' => array(
                'description' => 'Управление зарегистрированными на сайте пользователями, редактирование их профилей и блокировка аккаунта',
                'link' => 'users',
                'icon' => 'users',
            ),
            'Шаблоны' => array(
                'description' => 'Управление шаблонами',
                'link' => 'templates',
                'icon' => 'folder_open',
            ),

//    'Видео' => array(
//        'description' => 'Управление видеозаписями, редактирование и удаление',
//        'link' => 'videos',
//        'icon' => 'videos',
//    ),
//    'Музыка' => array(
//        'description' => 'Управление аудиозаписями, редактирование и удаление',
//        'link' => 'musics',
//        'icon' => 'music',
//    ),
//    'Альбомы' => array(
//        'description' => 'Управление альбомами, редактирование и удаление',
//        'link' => 'albums',
//        'icon' => 'photos',
//    ),
//    'Заметки' => array(
//        'description' => 'Управления заметками, которые опубликовали пользователи сайта',
//        'link' => 'notes',
//        'icon' => 'notes',
//    ),
//    'Подарки' => array(
//        'description' => 'Управление подарками на сайте, добавление, редактирование и удаление',
//        'link' => 'gifts',
//        'icon' => 'gifts',
//    ),
            'Сообщества' => array(
                'description' => 'Управление сообществами, редактирование и удаление',
                'link' => 'groups',
                'icon' => 'groups',
            ),
//    'Жалобы' => array(
//        'description' => $new_report . 'Список жалоб, поступивших от посетителей сайта на фотографии, записи, видеозаписи или заметки',
//        'link' => 'report',
//        'icon' => 'report',
//    ),
//    'Шаблоны сообщений' => array(
//        'description' => 'Настройка шаблонов E-Mail сообщений, которые отсылает скрипт с сайта при уведомлении.',
//        'link' => 'mail_tpl',
//        'icon' => 'mail_tpl',
//    ),
//    'Рассылка сообщений' => array(
//        'description' => 'Создание и массовая отправка E-Mail сообщений, для зарегистрированных пользователей',
//        'link' => 'mail',
//        'icon' => 'mail',
//    ),
//    'Фильтр по: IP' => array(
//        'description' => 'Блокировка доступа на сайт для определенных IP',
//        'link' => 'ban',
//        'icon' => 'ban',
//    ),
//    'Поиск и Замена' => array(
//        'description' => 'Быстрый поиск и замена определенного текста по всей базе данных',
//        'link' => 'search',
//        'icon' => 'search',
//    ),
//    'Статические страницы' => array(
//        'description' => 'Создание и редактирование страниц, которые как правило редко изменяются и имеют постоянный адрес',
//        'link' => 'static',
//        'icon' => 'static',
//    ),
//    'Логи посещений' => array(
//        'description' => 'Вывод IP и браузера пользователей при последнем входе на сайт',
//        'link' => 'logs',
//        'icon' => 'logs',
//    ),
//    'Страны' => array(
//        'description' => 'Добавление, удаление и редактирование стран',
//        'link' => 'country',
//        'icon' => 'country',
//    ),
//    'Города' => array(
//        'description' => 'Добавление, удаление и редактирование городов',
//        'link' => 'city',
//        'icon' => 'city',
//    ),
//    'Отзывы' => array(
//        'description' => $new_reviews . 'Модерация и удаление отзывов.',
//        'link' => 'reviews',
//        'icon' => 'reviews',
//    ),
//    'Отчеты по SMS' => array(
//        'description' => 'Просмотр отчетов отправки SMS от пользователей',
//        'link' => 'sms',
//        'icon' => 'sms',
//    ),
        ];
        return view('admin.main', $params);
    }
}