<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\repos\UsersRepository;
use Fhtechnikum\Webshop\services\UsersService;
use Fhtechnikum\Webshop\views\JSONView;
use PDO;

class UsersController implements ControllerInterface
{
    private PDO $usersDatabase;
    private UsersRepository $userssRepository;
    private UsersService $usersService;
    private JSONView $jsonView;

    public function __construct($database)
    {
        $this->usersDatabase = $database;
        $this->userssRepository = new UsersRepository($database);
        $this->usersService = new UsersService();
        $this->jsonView = new JSONView();
    }

    public function route()
    {
        // TODO: Implement route() method.
    }
}