<?php

namespace Fhtechnikum\Webshop\models;

class CartProductModel
{
    public ProductModel $productModel;
    public int $amount = 0;

    public function __construct(ProductModel $productModel)
    {
        $this->productModel = $productModel;
    }
}