<?php

namespace Fhtechnikum\Webshop\models;

class UserModel
{
    public string $userName;
    public string $passwordHash;

    /*public function __construct($userName, $passwordHash)
    {
        $this->userName = $userName;
        $this->passwordHash = $passwordHash;
    }*/

    public function isPasswordValid($passWord): bool
    {
        return password_verify($passWord, $this->passwordHash);
    }
}