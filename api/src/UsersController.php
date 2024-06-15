<?php

namespace Fhtechnikum\Webshop;

use Exception;
use Fhtechnikum\Webshop\repos\UsersRepository;
use Fhtechnikum\Webshop\services\UsersService;
use Fhtechnikum\Webshop\views\JSONView;
use InvalidArgumentException;
use PDO;
use Random\RandomException;

class UsersController implements ControllerInterface
{
    private PDO $usersDatabase;
    private UsersRepository $usersRepository;
    private UsersService $usersService;
    private JSONView $jsonView;

    public function __construct($database)
    {
        $this->usersDatabase = $database;
        $this->usersRepository = new UsersRepository($this->usersDatabase);
        $this->usersService = new UsersService($this->usersRepository);
        $this->jsonView = new JSONView();
    }

    /**
     * @throws RandomException
     */
    public function route(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getOrders();
                break;
            case 'POST':
                $this->postCredentialsOrOrders();
                break;
            default:
                throw new InvalidArgumentException("Unsupported HTTP request method");
        }
    }

    private function getOrders()
    {
        try {
            $resource = $this->getRequestParameter('resource', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            if (strtolower($resource) !== "orders") {
                throw new InvalidArgumentException("Invalid value for resource parameter");
            }

            $this->validateAuthorizationToken();
            $this->getOrdersHistory();

        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
            exit;
        }
    }
    private function validateAuthorizationToken(): void
    {
        $token = $this->getAuthorizationToken();
        try {
            if (!$this->usersService->validateToken($token)) {
                http_response_code(401); // Unauthorized
                echo json_encode(['error' => 'Unauthorized']);
                exit; // Exit if token is invalid
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit; // Exit on token validation failure with error
        }
    }
    private function getAuthorizationToken(): string
    {
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        throw new InvalidArgumentException('Invalid or missing authorization header');
    }

    /**
     * @throws RandomException
     */
    private
    function postCredentialsOrOrders(): void
    {
        $input = $this->getInputValue();

        switch (strtolower($input)) {
            case "login":
                $userName = $this->getRequestParameter('user-name', FILTER_SANITIZE_EMAIL);
                $password = $this->getRequestParameter('password', FILTER_SANITIZE_STRING);
                $this->handleLogin($userName, $password);
                break;
            case "logout":
                $this->usersService->logoutUser();
                break;
            case "orders":
                $token = $this->getRequestParameter('token', FILTER_SANITIZE_STRING);
                //TODO: token wahrscheinlich von woanders holen als einfach aus Adresszeile?
                $this->placeOrder($token);
            default:
                throw new InvalidArgumentException("Invalid value for action parameter");
        }
    }

    private
    function handleLogin($userName, $password): void
    {
        try {
            $token = $this->usersService->loginUser($userName, $password);
            echo json_encode(['token' => $token]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private
    function placeOrder(mixed $token)
    {
    }

    private
    function getRequestParameter($parameter, $filter, $options = null)
    {
        $value = filter_input(INPUT_GET, $parameter, $filter, $options);
        if ($value === null || $value === false) {
            throw new InvalidArgumentException("Invalid or missing $parameter parameter");
        }
        return $value;
    }

    private
    function getInputValue()
    {
        $input = null;
        $inputs = [
            'resource' => $this->getRequestParameter('resource', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES),
            'action' => $this->getRequestParameter('action', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)
        ];

        foreach ($inputs as $value) {
            if ($value !== null) {
                $input = $value;
                break;
            }
        }
        return $input;
    }

    private function getOrdersHistory(): void
    {
        $orderHistory = $this->usersService->getOrdersHistory();
        $this->jsonView->output($orderHistory);
    }
}