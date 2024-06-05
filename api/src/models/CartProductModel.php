<?php

namespace Fhtechnikum\Webshop\models;

use mysql_xdevapi\Expression;

class CartProductModel
{
    public ProductModel $productModel;
    public int $amount = 0;

    private float $itemTotal;

    public function __construct(ProductModel $productModel)
    {
        $this->productModel = $productModel;
    }

    public function getItemTotal($productModel): float{
        $this->itemTotal = $productModel->price * $this->amount;
        return $this->itemTotal;
    }

}