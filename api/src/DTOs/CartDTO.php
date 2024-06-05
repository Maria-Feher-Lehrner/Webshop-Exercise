<?php

namespace Fhtechnikum\Webshop\DTOs;

class CartDTO
{
    public array $cart;
    public float $totalPrice = 0.0;

    public static function map($cartModel): CartDTO{
        $cartDTO = new CartDTO();
        //$cartDTO->totalPrice = $cartDTO->getTotalPrice($cartModel);
        $cartDTO->cart = [];

        foreach ($cartModel->getProducts() as $cartProduct){
            $itemTotal = $cartProduct->getItemTotal();
            $cartDTO->cart[] = [
                "articleId" => $cartProduct->productModel->productId,
                "articleName" => $cartProduct->productModel->name,
                "amount" => $cartProduct->amount,
                "itemTotal" => $itemTotal
            ];
            $cartDTO->totalPrice += $itemTotal;
        }
        return $cartDTO;
    }

    public function getTotalPrice($cartModel): float{

        foreach ($cartModel->getProducts() as $cartProduct){
            $this->totalPrice += $cartProduct->getItemTotal($cartProduct);
        }
        return $this->totalPrice;
    }
}

//TODO: fix transmission of itemTotals and cartTotal