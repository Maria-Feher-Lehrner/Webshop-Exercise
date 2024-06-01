<?php

namespace Fhtechnikum\Webshop\repos;

use Fhtechnikum\Webshop\models\CartModel;
use PDO;

class CartRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function createNewCart(): CartModel{
        return new CartModel();
    }

    public function getAllProducts(): array
    {
        $query = "SELECT p.name AS productName, p.id AS productId, p.price_of_sale AS productPrice
        FROM products p";
        $statement = $this->database->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}