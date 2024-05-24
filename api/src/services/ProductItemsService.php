<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\DTOs\ProductsDTO;
use Fhtechnikum\Webshop\repos\ProductsRepository;

class ProductItemsService
{
    private array $productList;

    public function __construct(ProductsRepository $productsRepository)
    {
        $this->productList = $productsRepository->getProducts();
    }

    public function provideItemsResult(): ProductsDTO
    {
        $DTO = new ProductsDTO();

        $DTO->productType = $this->productList[0]["productTypeName"];
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
                $products[] = ['name' => $product['productName']];
            }
        }
        return $products;
    }
}