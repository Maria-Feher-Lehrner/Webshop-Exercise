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
        //ACHTUNG!! An der Stelle in Musterlösung: die() falls user unauthorized.

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
            $userName = $this->determineRequestParameter('username', FILTER_SANITIZE_EMAIL);
            $password = $this->determineRequestParameter('password', FILTER_SANITIZE_STRING);
            //ACHTUNG!! Hier aufpassen beim Entgegennehmen des Passworts: nicht filtern (?), weil man ja will,
            // dass der User alle Sonderzeichen für sein PW verwenden kann. Aber hier wird das PW sowieso über ein prepared Statement überprüft.

            $loginResult = $this->usersService->loginUser($userName, $password);
            $this->jsonView->output($loginResult);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function handleLogout(): void
    {
        try {
            $logoutResult = $this->usersService->logoutUser();
            $this->jsonView->output($logoutResult);
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
            //ACHTUNG!! Hier in Musterlösung genauer anschauen: Bearer wird bei Übernahme aus Spezifikation entfernt?
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

//TODO: Implementierung mit Java Web Token probieren --> jwt.io (kann man auch verschlüsseln)
//Empfehlung Passwortverschlüsselung: über das SQL Statement
//Andere Aufteilung Controller: AuthenticationController vor allem vorlagern. Dann ProductlistController, OrderController, Cartcontroller

//Ad Musterlösung (AuthenticationController): Ein Trait in PHP ist wie eine mix-in Klasse in Java
//In Mix-in Klassen sammelt man Funktionen - ist ein workaround, ohne Vererbung zu verwenden. Wird bei der Anwendung mit AuthenticationController eingesetzt,
//um eine zusätzliche Schicht einzuziehen.
//Trait = Sammlung von verschiedenen - allgemein praktischen Funktionen, die potenziell von mehreren Klassen aufgerufen werden können - ohne aber, dass diese Klassen logisch von der Trait Klasse erben.


//Bei Übername von token von Frontend: Spezifikation "Bearer" aus dem Header entfernen, damit nur noch token überbleibt