<?php

namespace Fhtechnikum\Webshop\models;

class ProductModel
{
    public int $productId;

    public string $name;
    public float $price;
    public string $categoryName;
    public int $categoryId;
}