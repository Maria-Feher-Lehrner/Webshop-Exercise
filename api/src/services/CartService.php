<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\models\CartModel;
use Fhtechnikum\Webshop\models\CartProductModel;
use Fhtechnikum\Webshop\models\ProductModel;
use Fhtechnikum\Webshop\repos\CartRepository;
use Fhtechnikum\Webshop\repos\ProductsRepository;

class CartService
{
    private array $allProducts;
    private CartModel $shoppingCart;

    public function __construct(ProductsRepository $productsRepository, CartModel $shoppingCart)
    {
        $this->allProducts = $productsRepository->getProducts();
        $this->shoppingCart = $shoppingCart;
    }

    public function addProduct(int $productId): void
    {
        $product = $this->allProducts[$productId];
        $productAmount = $product->amount;

        if ($this->isProductInCart($productId)){
            $productAmount++;
        }
        $this->shoppingCart->addProduct($product);
        $productAmount = 1;
    }

    public function removeProduct(int $productId): void{
        $product = $this->allProducts[$productId];
        $productAmount = $product->amount;

        if ($this->isProductInCart($productId)){
            $productAmount--;
        }
        if($productAmount == 0){
            $this->shoppingCart->removeProduct($product);
        }
    }

    private function isProductInCart(int $productId): bool
    {
        foreach ($this->shoppingCart->getProducts() as $cartProduct) {
            if ($cartProduct->product->productId == $productId) {
                return true;
            }
        }
        return false;
    }

    public function getCartContents()
    {
    }
}