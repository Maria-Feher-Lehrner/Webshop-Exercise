<?php

namespace Fhtechnikum\Webshop\models;

class CartModel
{
    private array $cartProducts = [];

    public function addProduct(CartProductModel $cartProduct): void
    {
        $this->cartProducts[] = $cartProduct;
    }

    public function getProducts(): array
    {
        return $this->cartProducts;
    }

    public function removeProduct(CartProductModel $cartProduct): void
    {
        $this->cartProducts = array_diff($this->cartProducts, [$cartProduct]);
        $this->cartProducts = array_values($this->cartProducts);
    }

}