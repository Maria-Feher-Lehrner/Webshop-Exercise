<?php

namespace Fhtechnikum\Webshop\DTOs;

class ProductDTO
{
    public int $productId;

    public string $name;
    public string $price;

    public static function map($productModel): ProductDTO
    {
        $productDTO = new ProductDTO();
        $productDTO->productId = $productModel->productId;
        $productDTO->name = $productModel->name;
        $productDTO->price = $productModel->price;

        return $productDTO;
    }
}