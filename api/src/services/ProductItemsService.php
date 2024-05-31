<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\DTOs\ProductsDTO;
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

    public function provideItemsResult(): ProductsDTO
    {
        $DTO = new ProductsDTO();

        $DTO->categoryType = $this->productList[0]["categoryName"];
        $DTO->categoryId = $this->productList[0]["categoryId"];
        $DTO->products = $this->buildItemsList();
        $DTO->url = "http://localhost/bb/Webshop/api/index.php?resource=types";

        return $DTO;
    }

    /**
     * @return array
     */
    public function buildItemsList(): array
    {
        $products = [];
        foreach ($this->productList as $product) {
            if ($product['productName'] !== null) {
                $products[] = [
                    'name' => $product['productName'],
                    'price' => $product['productPrice'],
                    'id' => $product['productId']
                ];
            }
        }
        return $products;
    }
}