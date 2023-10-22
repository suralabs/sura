<?php

namespace Mozg\modules;

use Mozg\classes\Module;

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