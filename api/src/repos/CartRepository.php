<?php

namespace Fhtechnikum\Webshop\repos;

use Fhtechnikum\Webshop\models\CartModel;

class CartRepository
{
    public function createNewCart(): CartModel{
        return new CartModel();
    }
}