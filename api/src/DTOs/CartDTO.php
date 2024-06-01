<?php

namespace Fhtechnikum\Webshop\DTOs;

class CartDTO
{
    public array $cart;

    public static function map($cartModel): CartDTO{
        $cartDTO = new CartDTO();
        $cartDTO->cart = [];

        foreach ($cartModel->getProducts() as $cartProduct){
            $cartDTO->cart[] = [
                "articleName" => $cartProduct->productModel->name,
                "amount" => $cartProduct->amount
            ];
        }
        return $cartDTO;
    }
}