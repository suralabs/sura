<?php

namespace Mozg\modules;

use Mozg\classes\Module;
use Sura\Http\Response;
use Sura\Support\Status;

class Newsfeed extends Module
{
    /**
     * @throws \JsonException
     */
    final public function main(): void
    {
        $response = array(
            'status' => '1',
        );

        (new Response)->_e_json($response);
    }

}