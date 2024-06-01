<?php

namespace Fhtechnikum\Webshop\models;

class CartModel
{
    public array $cartProducts = [];

    public function addProduct(CartProductModel $cartProduct): void
    {
        $this->cartProducts[] = $cartProduct;
    }

    public function getProducts(): array
    {
        return $this->cartProducts;
    }

    public function removeProductFromCart(CartProductModel $cartProduct): void
    {
        foreach ($this->cartProducts as $key => $existingCartProduct) {
            if ($existingCartProduct->productModel->productId === $cartProduct->productModel->productId) {
                unset($this->cartProducts[$key]);
                // Re-index the array
                $this->cartProducts = array_values($this->cartProducts);
                return;
            }
        }
    }

}