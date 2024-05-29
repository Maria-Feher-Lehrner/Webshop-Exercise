<?php

namespace Fhtechnikum\Webshop\repos;

use Fhtechnikum\Webshop\models;
use PDO;

class ProductsRepository
{
    private PDO $database;
    private int $typeId;

    public function __construct(PDO $database, int $typeId)
    {
        $this->database = $database;
        $this->typeId = $typeId;
    }

    /**
     * @return models\ProductModel[]
     * @return array
     */
    public function getProducts(): array
    {
        $query = "SELECT t.name AS productTypeName, p.name AS productName, p.id AS productId 
        FROM product_types t 
        LEFT JOIN products p ON t.id = p.id_product_types
        WHERE t.id = :id";
        $statement = $this->database->prepare($query);
        $statement->bindParam(":id", $this->typeId);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}