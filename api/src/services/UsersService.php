<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\DTOs\OrderHistoryDTO;
use Fhtechnikum\Webshop\repos\UsersRepository;
use InvalidArgumentException;
use Random\RandomException;

class UsersService
{
    private UsersRepository $usersRepository;
    private $customerId;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->customerId = $_SESSION['user_id'] ?? null;
    }

    /**
     * @throws RandomException
     */
    public function loginUser($userName, $passWord): array
    {
        $userModel = $this->usersRepository->getUserByEmail($userName);

        if ($userModel !== null && $userModel->isPasswordValid($passWord)) {
            $token = bin2hex(random_bytes(16));
            $_SESSION['user_token'] = $token;
            $_SESSION['user_email'] = $userName;
            $_SESSION['user_id'] = $userModel->userId;

            $state = "OK";
            //return $token;

        } else {
            //throw new InvalidArgumentException('Invalid email or password');
            $state = "ERROR";
            $token = null;
        }
        return ['state' => $state, 'token' => $token];
    }
    public function logoutUser(): array
    {
        session_unset();
        session_destroy();

        if (empty($_SESSION)) {
            return ['state' => 'OK'];
        } else {
            return ['state' => 'ERROR'];
        }
    }
    public function validateToken($token): bool
    {
        if (isset($_SESSION['user_token']) && $_SESSION['user_token'] === $token) {
            return true;
        }
        return false;
    }

    public function getOrdersHistory(): OrderHistoryDTO
    {
        $orderHistory = $this->usersRepository->getUserCartHistory($this->customerId);
        $orderHistoryDTO = new OrderHistoryDTO();
        foreach ($orderHistory as $order) {
            $orderHistoryDTO->orders[] = $order;
        }
        return $orderHistoryDTO;
    }

    public function placeOrder(): void
    {
        $cart = $_SESSION['shopping_cart'] ?? null;

        //TODO: ändern auf check, ob PRODUKTLISTE empty ist. Cart selbst kann kaum unset sein,
        // weil es automatisch zu Beginn des Controllers instanziert wird. Evtl. falls customer Bestellung aufgibt und
        // dann während Session noch läuft weitere Bestellung aufgeben möchte?
        /*if(!$cart){
            throw new InvalidArgumentException('Cart is empty');
        }*/


        $totalPrice = array_reduce($cart->getProducts(), function($sum, $cartProduct) {
            return $sum + $cartProduct->getItemTotal();
        }, 0.0);
        //$customerId = $_SESSION['user_id'] ?? null;

        $orderId = $this->usersRepository->createEntryOrderAndReturnOrderId($this->customerId, $totalPrice);

        foreach ($cart->getProducts() as $cartProduct) {
            $this->usersRepository->createEntryOrderPosition(
                $orderId,
                $cartProduct->productModel->productId,
                $cartProduct->amount,
                $cartProduct->getItemTotal()
            );
        }
        unset($_SESSION['shopping_cart']);
    }
}