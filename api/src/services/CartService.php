<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\models\CartModel;
use Fhtechnikum\Webshop\models\CartProductModel;
use Fhtechnikum\Webshop\models\ProductModel;
use Fhtechnikum\Webshop\repos\ProductsRepository;

class CartService
{
    private array $allProducts;
    private array $allProductModels;
    private CartModel $shoppingCart;

    public function __construct(ProductsRepository $productsRepository, CartModel $shoppingCart)
    {
        $this->allProducts = $productsRepository->getAllProducts();
        $this->allProductModels = $productsRepository->getMappedProducts($this->allProducts);
        $this->shoppingCart = $shoppingCart;
    }

    public function addProductToCart(int $productId): void
    {
        $product = $this->findProduct($productId);
        if ($product === null) {
            throw new \InvalidArgumentException("Product not found");
        }

        // Check if the product is already in the cart
        foreach ($this->shoppingCart->getProducts() as $cartProduct) {
            if ($cartProduct->productModel->productId === $productId) {
                // If the product is already in the cart, increase the amount
                $cartProduct->amount += 1;
                return;
            }
        }

        // If the product is not in the cart, create a new CartProductModel and add it to the cart
        $cartProduct = new CartProductModel($product);
        $cartProduct->amount = 1;
        $this->shoppingCart->addProduct($cartProduct);
    }

    public function removeProduct(int $productId): void
    {
        // Check if the product is already in the cart
        foreach ($this->shoppingCart->getProducts() as $cartProduct) {
            if ($cartProduct->productModel->productId === $productId) {
                // If the product is already in the cart, decrease the amount
                $cartProduct->amount -= 1;
                $this->deleteIfAmountZero($cartProduct);

                return;
            }
        }
    }

    public function getShoppingCart(): CartModel
    {
        return $this->shoppingCart;
    }

    private function deleteIfAmountZero($cartProduct): void
    {
        if ($cartProduct->amount <= 0) {
            $this->shoppingCart->removeProductFromCart($cartProduct);
        }
    }

    private function findProduct(int $productId): ?ProductModel
    {
        foreach ($this->allProductModels as $productModel) {
            if ($productModel->productId == $productId) {
                return $productModel;
            }
        }
        return null;
    }
}