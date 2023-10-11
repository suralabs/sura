<?php

namespace Mozg\modules;

class Home extends \Mozg\classes\Module
{
    /**
     * @throws \JsonException|\ErrorException
     */
    public function main()
    {
<<<<<<< HEAD
        $logged = $this->logged;
        if ($logged === true) {
            (new News)->main();
        } else {
            (new Register)->main();
        }
=======
        (new Register)->main();
>>>>>>> semyon492-dev
    }
}