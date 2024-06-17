<?php

namespace Fhtechnikum\Webshop;

use PDO;

class App
{
    private ?string $input = null;
    private PDO $database;

    public function __construct()
    {
        //initializing database connection
        $this->database = new PDO("mysql:host=localhost;dbname=bb_uebung_3; charset=utf8", "root", "");
    }

    public function start(): void
    {
        $this->validateInput();

        $controller = $this->identifyController($this->input);
        $controller->route();
    }

    private function validateInput(): void
    {
        $inputs = [
            'resource' => filter_input(INPUT_GET, 'resource', FILTER_SANITIZE_STRING),
            'action' => filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING)
        ];

        foreach ($inputs as $value) {
            if ($value !== null) {
                $this->input = $value;
                break;
            }
        }

        if ($this->input === null) {
            http_response_code(404);
            die('Invalid request');
        }
    }

    private function identifyController($input)
    {
        switch (strtolower($input)) {
            case "types":
            case "products":
                return new ProductDbController($this->database);
            case "cart":
                return new CartController($this->database);
            case "orders":
            case "login":
            case "logout":
                return new UsersController($this->database);
            default:
                http_response_code(404);
                die();
        }
    }
}