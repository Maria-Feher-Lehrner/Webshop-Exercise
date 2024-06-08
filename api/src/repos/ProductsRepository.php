<?php

namespace Fhtechnikum\Webshop\repos;

use Fhtechnikum\Webshop\models\CartModel;
use Fhtechnikum\Webshop\models\ProductModel;
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

    public function getAllProducts(): array
    {
        $query = "SELECT t.name AS categoryName, t.id AS categoryId, p.name AS productName, p.id AS productId, p.price_of_sale AS productPrice
        FROM product_types t 
        LEFT JOIN products p ON t.id = p.id_product_types";
        $statement = $this->database->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductsByTypeId(int $typeId): array
    {
        $query = "SELECT t.name AS categoryName, t.id AS categoryId, p.name AS productName, p.id AS productId, p.price_of_sale AS productPrice
        FROM product_types t 
        LEFT JOIN products p ON t.id = p.id_product_types
        WHERE t.id = :id";

        $statement = $this->database->prepare($query);
        $statement->bindParam(":id", $typeId);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        //Include category-Infos if category contains no products/result is empty

        if (empty($result)) {
            $categoryQuery = "SELECT name AS categoryName, id AS categoryId FROM product_types WHERE id = :id";
            $categoryStatement = $this->database->prepare($categoryQuery);
            $categoryStatement->bindParam(":id", $typeId);
            $categoryStatement->execute();
            $categoryInfo = $categoryStatement->fetch(PDO::FETCH_ASSOC);

            if ($categoryInfo) {
                $result[] = $categoryInfo + ['productName' => null, 'productId' => null, 'productPrice' => null];
            }
        }
        return $result;
    }

    public function createNewCart(): CartModel{
        return new CartModel();
    }

    /**
     * @return array|models\ProductModel[]
     */
    public function getMappedProducts($productList): array
    {
        $productModelList = [];
        foreach ($productList as $product) {
            if ($product['productName'] !== null) {
                $productModel = new ProductModel();
                $productModel->name = $product['productName'];
                $productModel->productId = $product['productId'];
                $productModel->price = (float) $product['productPrice'];
                $productModel->categoryName = $product['categoryName'];
                $productModel->categoryId = $product['categoryId'];

                $productModelList[] = $productModel;
            }
        }
        return $productModelList;
    }
}