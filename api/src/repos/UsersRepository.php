<?php

namespace Fhtechnikum\Webshop\repos;

use Fhtechnikum\Webshop\models\UserModel;
use PDO;

class UsersRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function getUserByEmail($email): ?UserModel
    {
        $query = "SELECT email as userName, password as passwordHash FROM customers
                    WHERE email = :email";
        $statement = $this->database->prepare($query);
        $statement->bindParam(":email", $email);
        $statement->execute();
        $userModel = $statement->fetchObject(UserModel::class);

        return $userModel ?: null;
    }

    public function getUserCartHistory(){

    }
}