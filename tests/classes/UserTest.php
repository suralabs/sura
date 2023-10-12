<?php

namespace classes;

use Mozg\classes\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    public function testUser()
    {
        $email = time().'pupkin@example.ru';
        User::addUser('Ivan', 'Pupkin', $email, 'Password2023');
        User::removeUser($email);
        self::assertTrue(true);
    }
}
