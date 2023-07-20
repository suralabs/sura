<?php
/*
 * Copyright (c) 2022 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace Mozg\classes;

use Mozg\modules\Lang;
use Sura\View\myView;

/**
 *
 */
class ViewEmail
{
    public string $message = '';

    /**
     * @throws \Exception
     */
    public function __construct(string $template, $variables)
    {
        $config = settings_get();
        $views = ROOT_DIR . '/templates/' . $config['temp'];
        $cache = ENGINE_DIR . '/cache/views';
        $blade = new myView($views, $cache, \Sura\View\View::MODE_AUTO); // MODE_DEBUG allows pinpointing troubles.
        $blade::$dictionary = I18n::dictionary();
        $this->message = $blade->run($template, $variables);
    }

    /**
     * @return string
     */
    final public function run(): string
    {
        return $this->message;
    }
}