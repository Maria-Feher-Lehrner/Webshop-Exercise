<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\DTOs\ProductListDTO;
use Fhtechnikum\Webshop\models\ProductModel;
use Fhtechnikum\Webshop\repos\ProductsRepository;

class ProductItemsService
{
    private int $typeId;
    private array $productList;

    public function __construct(int $typeId, ProductsRepository $productsRepository)
    {
        $this->typeId = $typeId;
        $this->productList = $productsRepository->getProducts($this->typeId);
    }

    /*public function provideItemsResult(): ProductListDTO
    {
        $DTO = new ProductListDTO();

        $DTO->categoryType = $this->productList[0]["categoryName"];
        $DTO->categoryId = $this->productList[0]["categoryId"];
        $DTO->products = $this->mapAndReturnItemsList();
        $DTO->url = "http://localhost/bb/Webshop/api/index.php?resource=types";

        return $DTO;
    }*/

    /**
     * @return array|models\ProductModel[]
     */
    public function mapAndReturnItemsList(): array
    {
        $productModelList = [];
        foreach ($this->productList as $product) {
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
}