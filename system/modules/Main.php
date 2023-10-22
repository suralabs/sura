<?php

namespace Mozg\modules;

class Main extends \Mozg\classes\Module
{
    /**
     * @throws \JsonException|\ErrorException
     */
    public function main()
    {
        $params = [];
        return view('main.home', $params);
    }
}