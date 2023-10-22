<?php

/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\classes;

use Sura\Support\Cookie;
use Sinergi\BrowserDetector\Language;

/**
 *
 */
class I18n
{
    /**
     *
     */
    public const EN = 'en';
    /**
     *
     */
    public const RU = 'ru';

    public static $lang_list = array(
        'en' => 'English',
        'ru' => 'Russian',
    );

    /**
     * get lang key
     * @return string
     */
    public static function getLang(): string
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $data['lang'] = $data['lang'] ?? self::EN;
        $curr_lang = (new \Sura\Http\Request)->textFilter((string)$data['lang']);
        if (!empty($curr_lang)) {
            if(isset(self::$lang_list[$curr_lang])){
                $use_lang = $curr_lang;
            }else{
                $use_lang = self::EN;
            }            
        } else {
            $use_lang = self::EN;
        }
        return $use_lang;
        //     $lang_list = self::langList();
        //     $lang_count = \count($lang_list);
        //     $lang_Id = (int)(Cookie::get('lang'));
        //     if ($lang_Id > $lang_count) {
        //         Cookie::append('lang', self::EN, 365);
        //         $use_lang = self::EN;
        //     } elseif (!empty($lang_Id)) {
        //         $use_lang = $lang_Id;
        //     } else {
        //         $language = new Language();
        //         if ($language->getLanguage() === 'en') {
        //             Cookie::append('lang', self::EN, 365);
        //             $use_lang = self::EN;
        //         } elseif ($language->getLanguage() === 'ru') {
        //             Cookie::append('lang', self::RU, 365);
        //             $use_lang = self::RU;
        //         } else {
        //             Cookie::append('lang', self::EN, 365);
        //             $use_lang = self::EN;
        //         }
        //     }
        //     return $lang_list[$use_lang]['key'];            
        // }
    }

    /**
     * Language dictionary
     * @return array dictionary list
     */
    public static function dictionary(): array
    {
        $file_name = '/site.php';
        return require ROOT_DIR . '/lang/' . self::getLang() . $file_name;
    }
}