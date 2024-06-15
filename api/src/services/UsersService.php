<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\DTOs\OrderHistoryDTO;
use Fhtechnikum\Webshop\repos\UsersRepository;
use InvalidArgumentException;
use Random\RandomException;

class UsersService
{
    private UsersRepository $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    /**
     * @throws RandomException
     */
    public function loginUser($userName, $passWord): string
    {
        if ($this->checkCredentials($userName, $passWord)) {
            $token = bin2hex(random_bytes(16));
            $_SESSION['user_token'] = $token;
            $_SESSION['user_email'] = $userName;
            //print_r($_SESSION);
            return $token;
        } else {
            throw new InvalidArgumentException('Invalid email or password');
        }
    }
    public function checkCredentials($email, $password): bool
    {
        $userModel = $this->usersRepository->getUserByEmail($email);

        if ($userModel !== null && $userModel->isPasswordValid($password)) {
            return true;
        }
        return false;
    }

    public function validateToken($token): bool
    {
        if (isset($_SESSION['user_token']) && $_SESSION['user_token'] === $token) {
            return true;
        }
        return false;
    }

    public function logoutUser(): void
    {
        session_unset();
        session_destroy();
    }

    public function getOrdersHistory(): OrderHistoryDTO
    {
        $orderHistory = new OrderHistoryDTO();
        return $orderHistory;
    }

    public function placeOrder()
    {
    }
}