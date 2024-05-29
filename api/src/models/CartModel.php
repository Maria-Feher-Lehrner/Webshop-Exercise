<?php

namespace Fhtechnikum\Webshop\models;

class CartModel
{
    private array $cartProducts = [];

    public function addProduct(CartProductModel $cartProduct)
    {
        $this->cartProducts[] = $cartProduct;
    }

    public function getProducts(): array
    {
        return $this->cartProducts;
    }
}