<?php

namespace Mozg\modules;

class Home extends \Mozg\classes\Module
{
    /**
     * @throws \JsonException|\ErrorException
     */
    public function main()
    {
        (new Register)->main();
    }
}