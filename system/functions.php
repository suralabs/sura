<?php

/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

use Sura\Support\Declensions;
use Sura\Support\Registry;
use Mozg\classes\View;
use Sura\Http\{Request, Response};
use JetBrains\PhpStorm\ArrayShape;
use Mozg\classes\Cache;
use Mozg\classes\I18n;

/**
 * @throws JsonException
 */
function informationText($array): string
{
    $db = Registry::get('db');
    $array = json_decode($array, 1, 512, JSON_THROW_ON_ERROR);
    $row = $db->super_query("SELECT user_name, user_last_name FROM  users WHERE user_id = '" . ($array['type'] == 1 ? $array['oid2'] : $array['oid']) . "'");
    if ($array['type'] == 5) {
        $row2 = $db->super_query("SELECT user_name, user_last_name FROM  users WHERE user_id = '" . $array['oid2'] . "'");
    } else {
        $row2['user_name'] = null;
    }

    $text = array(
        0 => $row['user_name'] . ' создал(а) беседу',
        1 => $row['user_name'] . ' приглашен(а) в беседу',
        2 => $row['user_name'] . ' покинул(а) беседу',
        3 => $row['user_name'] . ' обновил(а) название беседы',
        4 => $row['user_name'] . ' обновил(а) фотографию беседы',
        5 => $row['user_name'] . ' исключил(а) участника "' . $row2['user_name'] . '"',);
    return $text[$array['type']];
}

/**
 * @param array|string $source
 * @return array|string
 */
function myBrRn(array|string $source): array|string
{
    $find[] = "<br />";
    $replace[] = "\r";
    $find[] = "<br />";
    $replace[] = "\n";
    return str_replace($find, $replace, $source);
}

/**
 * @param array|string $source
 * @return array|string
 */
function rn_replace(array|string $source): array|string
{
    $find[] = "'\r'";
    $replace[] = "";
    $find[] = "'\n'";
    $replace[] = "";
    return preg_replace($find, $replace, $source);
}

/**
 * @param $user_year
 * @param $user_month
 * @param $user_day
 * @return false|string|void
 */
function user_age($user_year, $user_month, $user_day)
{
    $server_time = time();
    if ($user_year) {
        $current_year = date('Y', $server_time);
        $current_month = date('n', $server_time);
        $current_day = date('j', $server_time);
        $current_str = strtotime($current_year . '-' . $current_month . '-' . $current_day);
        $current_user = strtotime($current_year . '-' . $user_month . '-' . $user_day);
        if ($current_str >= $current_user) {
            $user_age = $current_year - $user_year;
        } else {
            $user_age = $current_year - $user_year - 1;
        }
        if ($user_month && $user_day) {

            return $user_age . ' ' . declWord($user_age, 'user_age');
        }

        return false;//fixme
    }
}

function declWord(int $num, string $type): string
{
    $lang = I18n::getLang();
    $decl_list = require ROOT_DIR . "/lang/{$lang}/declensions.php";
    return (new Declensions($decl_list))->makeWord($num, $type);
}

/**
 * @param $source
 * @return string
 */
function grammaticalName($source): string
{
    $name_u_gram = $source;
    $str_1_name = strlen($name_u_gram);
    $str_2_name = $str_1_name - 2;
    $str_3_name = substr($name_u_gram, $str_2_name, $str_1_name);
    $str_5_name = substr($name_u_gram, 0, $str_2_name);
    $str_4_name = strtr($str_3_name, array(
        'ай' => 'ая', 'ил' => 'ила', 'др' => 'дра', 'ей' => 'ея', 'кс' => 'кса', 'ша' => 'ши', 'на' => 'ны',
        'ка' => 'ки', 'ад' => 'ада', 'ма' => 'мы', 'ля' => 'ли', 'ня' => 'ни', 'ин' => 'ина', 'ик' => 'ика',
        'ор' => 'ора', 'им' => 'има', 'ём' => 'ёма', 'ий' => 'ия', 'рь' => 'ря', 'тя' => 'ти', 'ся' => 'си',
        'из' => 'иза', 'га' => 'ги', 'ур' => 'ура', 'са' => 'сы', 'ис' => 'иса', 'ст' => 'ста', 'ел' => 'ла',
        'ав' => 'ава', 'он' => 'она', 'ра' => 'ры', 'ан' => 'ана', 'ир' => 'ира', 'рд' => 'рда', 'ян' => 'яна',
        'ов' => 'ова', 'ла' => 'лы', 'ия' => 'ии', 'ва' => 'вой', 'ыч' => 'ыча', 'ич' => 'ича'));
    return $str_5_name . $str_4_name;
}

function GenerateAlbumPhotosPosition($uid, $aid = false)
{
    $db = Registry::get('db');
    //Выводим все фотографии из альбома и обновляем их позицию только для просмотра альбома
    if ($uid and $aid) {
        $sql_ = $db->super_query("SELECT id FROM `photos` WHERE album_id = '{$aid}' ORDER by `position` ASC", true);
        $count = 1;
        $photo_info = '';
        foreach ($sql_ as $row) {
            $db->query("UPDATE LOW_PRIORITY `photos` SET position = '{$count}' WHERE id = '{$row['id']}'");
            $photo_info.= $count . '|' . $row['id'] . '||';
            $count++;
        }
        Cache::mozgCreateCache('user_' . $uid . '/position_photos_album_' . $aid, $photo_info);
    }
}
function CheckFriends($friendId): bool
{
    $user_info = Registry::get('user_info');
    /** @var string $user_info['user_id'] */
    $open_my_list = Cache::mozgCache("user_{$user_info['user_id']}/friends");
    return stripos($open_my_list, "u{$friendId}|") !== false;
}
function CheckBlackList($userId): bool
{
    $user_info = Registry::get('user_info');
    $open_my_list = Cache::mozgCache("user_{$userId}/blacklist");
    /** @var string $user_info['user_id'] */
    return stripos($open_my_list, "|{$user_info['user_id']}|") !== false;
}
function MyCheckBlackList($userId): bool
{
    $user_info = Registry::get('user_info');
    /** @var string $user_info['user_id'] */
    $open_my_list = Cache::mozgCache("user_{$user_info['user_id']}/blacklist");
    return stripos($open_my_list, "|{$userId}|") !== false;
}

/**
 * @param $source
 * @param bool $encode
 * @return array|mixed|string|string[]|null
 */
function word_filter($source, bool $encode = true)
{
    global $config;
    $safe_mode = false;
    if ($encode) {
        $all_words = @file(ENGINE_DIR . '/data/wordfilter.db.php');
        $find = array();
        $replace = array();
        if (!$all_words or !count($all_words)) return $source;
        foreach ($all_words as $word_line) {
            $word_arr = explode("|", $word_line);
            if (function_exists("get_magic_quotes_gpc")) {
                $word_arr[1] = addslashes($word_arr[1]);
            }
            if ($word_arr[4]) {
                $register = "";
            } else $register = "i";
            if ($config['charset'] == "utf-8") $register.= "u";
            $allow_find = true;
            if ($word_arr[5] == 1 AND $safe_mode) $allow_find = false;
            if ($word_arr[5] == 2 AND !$safe_mode) $allow_find = false;
            if ($allow_find) {
                if ($word_arr[3]) {
                    $find_text = "#(^|\b|\s|\<br \/\>)" . preg_quote($word_arr[1], "#") . "(\b|\s|!|\?|\.|,|$)#" . $register;
                    if ($word_arr[2] == "") $replace_text = "\\1";
                    else $replace_text = "\\1<!--filter:" . $word_arr[1] . "-->" . $word_arr[2] . "<!--/filter-->\\2";
                } else {
                    $find_text = "#(" . preg_quote($word_arr[1], "#") . ")#" . $register;
                    if ($word_arr[2] == "") $replace_text = "";
                    else $replace_text = $word_arr[2];
                }
                if ($word_arr[6]) {
                    if (preg_match($find_text, $source)) {
                        return $source;
                    }
                } else {
                    $find[] = $find_text;
                    $replace[] = $replace_text;
                }
            }
        }
        if (!count($find)) return $source;
        $source = preg_split('((>)|(<))', $source, -1, PREG_SPLIT_DELIM_CAPTURE);
        $count = count($source);
        for ($i = 0;$i < $count;$i++) {
            if ($source[$i] == "<" or $source[$i] == "[") {
                $i++;
                continue;
            }
            if ($source[$i] != "") $source[$i] = preg_replace($find, $replace, $source[$i]);
        }
        $source = join("", $source);
    } else {
        $source = preg_replace("#<!--filter:(.+?)-->(.+?)<!--/filter-->#", "\\1", $source);
    }
    return $source;
}

function normalizeName(string $value, bool $part = true): array|null|string
{
    $value = str_replace(chr(0), '', $value);

    $value = trim(strip_tags($value));
    $value = preg_replace("/\s+/u", "-", $value);
    if (empty($value)) {
        return null;
    }
    $value = str_replace("/", "-", $value);
    if ($part) {
        $value = preg_replace("/[^a-z0-9\_\-.]+/mi", "", $value);
    } else {
        $value = preg_replace("/[^a-z0-9\_\-]+/mi", "", $value);
    }
    if (empty($value)) {
        return null;
    }
    $value = preg_replace('#[\-]+#i', '-', $value);
    return preg_replace('#[.]+#i', '.', $value);
}

/**
 * @return array
 */
function settings_get(): array
{
    if (Registry::exists('config')) {
        return Registry::get('config');
    }
    try {
        $config = require __DIR__ . '/data/config.php';
        Registry::set('config', $config);
        return $config;
    } catch (Error) {
        echo 'Please install system';
        exit();
    }
}

/**
 * @return array
 */
function settings_get_db(): array
{
    if (Registry::exists('config_db')) {
        return Registry::get('config_db');
    }
    try {
        $config = require __DIR__ . '/data/db_config.php';
        Registry::set('config_db', $config);
        return $config;
    } catch (Error) {
        echo 'Please install system';
        exit();
    }
}

/**
 * @deprecated
 * @throws JsonException
 */
function compileAdmin($tpl): void
{
    $tpl->load_template('main.tpl');
    $config = settings_get();
    $admin_index = $config['admin_index'];
    $admin_link = $config['home_url'] . $config['admin_index'];
    if (Registry::get('logged')) {
        $stat_lnk = "<a href=\"{$admin_index}?mod=stats\" onclick=\"Page.Go(this.href); return false;\" style=\"margin-right:10px\">статистика</a>";
        $exit_lnk = "<a href=\"#\" onclick=\"Logged.log_out()\">выйти</a>";
    } else {
        $stat_lnk = '';
        $exit_lnk = '';
    }

    $box_width = 800;

    $tpl->set('{admin_link}', $admin_link);
    $tpl->set('{admin_index}', $admin_index);
    $tpl->set('{box_width}', $box_width);
    $tpl->set('{stat_lnk}', $stat_lnk);
    $tpl->set('{exit_lnk}', $exit_lnk);
    $tpl->set('{content}', $tpl->result['content']);
    $tpl->compile('main');
    if ((new Request)->filter('ajax') === 'yes') {
        $metatags['title'] = 'Панель управления';
        $result_ajax = array(
            'title' => $metatags['title'],
            'content' => $tpl->result['info'] . $tpl->result['content']
        );
        (new Response)->_e_json($result_ajax);
    } else {
        echo $tpl->result['main'];
    }
}

/**
 * @param string|null $view
 * @param array $variables
 * @return bool
 * @throws ErrorException
 * @throws JsonException|Exception
 */
function view(?string $view, array $variables = []): bool
{
    try {
        echo (new View())->render($view, $variables);
        return true;
    } catch (Error) {
        return false;
    }
}

function view_json(?string $view, array $variables = []): string
{
    try {
        return (new View())->render($view, $variables);
    } catch (Error|Exception) {
        return 'err 500';
    }
}

/**
 * Device info
 * @return array
 */
#[ArrayShape(['browser' => 'string',
    'browser_ver' => 'string',
    'operating_system' => 'string',
    'device ' => 'string',
    'language ' => 'string'])]
function get_device(): array
{
    $browser = new \Sinergi\BrowserDetector\Browser();
    $operating_system = new \Sinergi\BrowserDetector\Os();
    $user_device = new \Sinergi\BrowserDetector\Device();
    $language = new \Sinergi\BrowserDetector\Language();

    return [
        'browser' => $browser->getName(),
        'browser_ver' => $browser->getVersion(),
        'operating_system' => $operating_system->getName(),
        'device ' => $user_device->getName(),
        'language ' => $language->getLanguage(),
    ];
}

/**
 * @param string $value
 * @param bool $lower
 * @param bool $part
 * @return array|string|null
 */
function to_translit(string $value, bool $lower = true, bool $part = true): array|string|null
{
    $lang_translit = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v',
        'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'j', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ь' => '', 'ы' => 'y', 'ъ' => '',
        'э' => 'je', 'ю' => 'ju', 'я' => 'ja',
        "ї" => "ji", "є" => "ye", "ґ" => "g",
        
        'А' => 'A', 'Б' => 'B', 'В' => 'V',
        'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'J', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
        'Э' => 'Je', 'Ю' => 'Ju', 'Я' => 'Ja',
        "Ї" => "Ji", "Є" => "ye", "Ґ" => "G",
        "À"=>"A", "à"=>"a", "Á"=>"A", "á"=>"a", 
        "Â"=>"A", "â"=>"a", "Ä"=>"A", "ä"=>"a", 
        "Ã"=>"A", "ã"=>"a", "Å"=>"A", "å"=>"a", 
        "Æ"=>"AE", "æ"=>"ae", "Ç"=>"C", "ç"=>"c", 
        "Ð"=>"D", "È"=>"E", "è"=>"e", "É"=>"E", 
        "é"=>"e", "Ê"=>"E", "ê"=>"e", "Ì"=>"I", 
        "ì"=>"i", "Í"=>"I", "í"=>"i", "Î"=>"I", 
        "î"=>"i", "Ï"=>"I", "ï"=>"i", "Ñ"=>"N", 
        "ñ"=>"n", "Ò"=>"O", "ò"=>"o", "Ó"=>"O", 
        "ó"=>"o", "Ô"=>"O", "ô"=>"o", "Ö"=>"O", 
        "ö"=>"o", "Õ"=>"O", "õ"=>"o", "Ø"=>"O", 
        "ø"=>"o", "Œ"=>"OE", "œ"=>"oe", "Š"=>"S", 
        "š"=>"s", "Ù"=>"U", "ù"=>"u", "Û"=>"U", 
        "û"=>"u", "Ú"=>"U", "ú"=>"u", "Ü"=>"U", 
        "ü"=>"u", "Ý"=>"Y", "ý"=>"y", "Ÿ"=>"Y", 
        "ÿ"=>"y", "Ž"=>"Z", "ž"=>"z", "Þ"=>"B", 
        "þ"=>"b", "ß"=>"ss", "£"=>"pf", "¥"=>"ien", 
        "І"=>"I", "і"=>"i", "ð"=>"eth", "ѓ"=>"r"
    );
    $value = str_ireplace( ".php", ".ppp", $value );
    $value = trim(strip_tags($value));
    $value = str_replace(chr(0), '', $value);
    $value = preg_replace( '/\-+/', '-', $value );
    $value = strtr($value, $lang_translit);
    if ($part)
        $value = preg_replace("/[^a-z0-9\_\-.]+/mi", "", $value);
    else
        $value = preg_replace("/[^a-z0-9\_\-]+/mi", "", $value);
    $value = preg_replace('#[\-]+#i', '-', $value);
    if ($lower)
        $value = strtolower($value);
    if (strlen($value) > 200) {
        $value = substr($value, 0, 200);
        if (($temp_max = strrpos($value, '-'))) $value = substr($value, 0, $temp_max);
    }
    return $value;
}