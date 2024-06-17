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
                $this->processUserSessionsAndOrders();
                break;
            default:
                throw new InvalidArgumentException("Unsupported HTTP request method");
        }
    }

    private function getOrders()
    {
        try {
            $resource = $this->determineRequestParameter('resource', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
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
    private function getOrdersHistory(): void
    {
        $orderHistory = $this->usersService->getOrdersHistory();

        $this->jsonView->output($orderHistory);
    }

    private function determineRequestParameter($parameter, $filter, $options = null)
    {
        $value = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $value = filter_input(INPUT_POST, $parameter, $filter, $options);
        }

        if ($value === null) {
            $value = filter_input(INPUT_GET, $parameter, $filter, $options);
        }
        return $value;
    }

    /**
     * @throws RandomException
     */
    private function processUserSessionsAndOrders(): void
    {
        try {
            $input = $this->getInputValue();

            switch (strtolower($input)) {
                case "login":
                    $this->handleLogin();
                    break;
                case "logout":
                    $this->handleLogout();
                    break;
                case "orders":
                    $this->placeOrder();
                    break;
                default:
                    throw new InvalidArgumentException("Invalid value for input parameter");
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function handleLogin(): void
    {
        try {
            $userName = $this->determineRequestParameter('user-name', FILTER_SANITIZE_EMAIL);
            $password = $this->determineRequestParameter('password', FILTER_SANITIZE_STRING);

            $loginResult = $this->usersService->loginUser($userName, $password);
            echo json_encode($loginResult);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function handleLogout(): void
    {
        try {
            $logoutResult = $this->usersService->logoutUser();
            echo json_encode($logoutResult);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    private function placeOrder(): void
    {
        $this->validateAuthorizationToken();
        $this->usersService->placeOrder();
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
        $headers = getallheaders();
        $authorizationHeader = $headers['Authorization'] ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
            $token = $matches[1];
            return $token;
        }

        throw new InvalidArgumentException('Invalid or missing authorization header');
    }


    private function getInputValue()
    {
        $input = null;
        $inputs = [
            'resource' => $this->determineRequestParameter('resource', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES),
            'action' => $this->determineRequestParameter('action', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)
        ];

        foreach ($inputs as $value) {
            if ($value !== null) {
                $input = $value;
                break;
            }
        }

        if ($input === null) {
            throw new InvalidArgumentException("Invalid or missing action/resource parameter");
        }
        return $input;
    }
}