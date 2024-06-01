<?php
/*
namespace Fhtechnikum\Webshop\repos;

use Fhtechnikum\Webshop\models\CartModel;
use Fhtechnikum\Webshop\models\ProductModel;
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

    /**
     * @return array|models\ProductModel[]

    public function mapProducts(): array
    {
        $productList = $this->getAllProducts();
        $productModelList = [];
        foreach ($productList as $product) {
            if ($product['productName'] !== null) {
                $productModel = new ProductModel();
                $productModel->name = $product['productName'];
                $productModel->productId = $product['productId'];
                $productModel->price = $product['productPrice'];
                $productModel->categoryName = $product['categoryName'];
                $productModel->categoryId = $product['categoryId'];

                $productModelList[] = $productModel;
            }
        }
        return $productModelList;
    }

}*/