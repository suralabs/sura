<?php

//TODO remove old routes
return array(
    // Главная
    '/' => 'HomeController@Index',

    '/theme/' => 'HomeController@Theme',
    '/404/' => 'HomeController@Index',
//    '/2'                                           => array('method' => 'HomeController@Index'),
    // Регистрация
    '/reg/' => 'HomeController@Index',
    '/test/' => 'HomeController@Test',


    '/register/' => 'AuthController@end_register',
    //Воостановление пароля
    '/restore/' => 'AuthController@restore',
    '/restore/next/' => 'AuthController@restore_next',
    '/restore/send/' => 'AuthController@restore_send',
    '/restore/finish/' => 'AuthController@restore_finish',
    '/restore/prefinish/' => 'AuthController@restore_pre_finish',
    //Реф. ссылка на регистрацию
    '/reg/:num/' => 'HomeController@Index',
    '/signup/' => 'AuthController@signup',
    '/login/' => 'AuthController@Login',
    '/logout/' => 'AuthController@logout',


    // Страница пользователя
    '/public:num' => 'PublicController@Index',
    '/u:num' => 'ProfileController@Index',

    '/u:num/after' => 'ProfileController@Index',

    // Редактирование страницы
//    '/edit/'                                      => 'EditprofileController@Index',
    '/edit/box/' => 'EditprofileController@box',
    '/edit/contact/' => 'EditprofileController@contact',
    '/edit/interests/' => 'EditprofileController@interests',
    '/edit/all/' => 'EditprofileController@all',
    '/edit/miniature/' => 'EditprofileController@miniature',
    '/edit/miniature_save/' => 'EditprofileController@miniature_save',
    '/edit/load_photo/' => 'EditprofileController@load_photo',//box upload ava
    '/edit/del_photo/' => 'EditprofileController@del_photo',
//    '/edit/delcover/'                             => 'EditprofileController@delcover',
//    '/edit/savecoverpos/'                         => 'EditprofileController@savecoverpos',
    '/edit/save_general/' => 'EditprofileController@save_general',
    '/edit/save_contact/' => 'EditprofileController@save_contact',
    '/edit/save_interests/' => 'EditprofileController@save_interests',
    '/edit/save_xfields/' => 'EditprofileController@save_xfields',
//    '/edit/upload_cover/'                         => 'EditprofileController@upload_cover',
    '/edit/upload/' => 'EditprofileController@upload',
    '/edit/country/' => 'EditprofileController@Index',
    '/edit/city/' => 'EditprofileController@Index',

);

