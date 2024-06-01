<?php

namespace Fhtechnikum\Webshop\repos;

use PDO;

class ProductsRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function getAllCategories(): array
    {
        $query = "SELECT id, name FROM product_types ORDER BY name";

        $statement = $this->database->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProducts(int $typeId): array
    {
        $query = "SELECT t.name AS categoryName, t.id AS categoryId, p.name AS productName, p.id AS productId, p.price_of_sale AS productPrice
        FROM product_types t 
        LEFT JOIN products p ON t.id = p.id_product_types
        WHERE t.id = :id";

        $statement = $this->database->prepare($query);
        $statement->bindParam(":id", $typeId);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}