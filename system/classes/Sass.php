<?php
/*
 * Copyright (c) 2022 Tephida
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\classes;

use ScssPhp\ScssPhp\Compiler;

class Sass
{
    public static function collect()
    {
        $compiler = new Compiler();

        $compiler->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);

        $config = settings_get();

        $scss_list = [
            'style' => ROOT_DIR . '/resources/views/' . $config['temp'] . '/scss/',
        ];

        foreach ($scss_list as $key => $patch) {
            self::build($compiler, $patch, $key);
        }
    }

    public static function build($compiler, $putch, $key)
    {
        $compiler->setImportPaths($putch);
        $file = $compiler->compileString(file_get_contents($putch . 'build.scss'))->getCss();
        file_put_contents(ROOT_DIR . '/public/assets/css/' . $key . '.css', $file);
        return true;
    }
}