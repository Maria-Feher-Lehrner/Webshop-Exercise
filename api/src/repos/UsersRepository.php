<?php

namespace Fhtechnikum\Webshop\repos;

use PDO;

class UsersRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function checkLoginData(){

    }

    public function getUserCartHistory(){

    }

}