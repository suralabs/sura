<?php

namespace Mozg\modules;

use JetBrains\PhpStorm\NoReturn;
use \Sura\Http\Response;
use \Sura\Http\Request;
use \Sura\Support\Status;
use Mozg\classes\{DB, Module, ViewEmail, Email};
use Intervention\Image\ImageManager;
use Sura\Filesystem\Filesystem;

class Api  extends Module
{
    /**
     * @throws \JsonException
     */
    final public function main(): void
    {
        $response = array(
            'status' => '1',
        );

        (new \Sura\Http\Response)->_e_json($response);
    }

}