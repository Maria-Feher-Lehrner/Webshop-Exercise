<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\repos\CartRepository;
use Fhtechnikum\Webshop\repos\CategoriesRepository;
use Fhtechnikum\Webshop\repos\ProductsRepository;
use Fhtechnikum\Webshop\services\CartService;
use Fhtechnikum\Webshop\services\CategoriesService;
use Fhtechnikum\Webshop\services\ProductItemsService;
use Fhtechnikum\Webshop\views\JSONView;
use http\Exception\InvalidArgumentException;
use PDO;

class ProductDbController
{
    private PDO $productDatabase;
    //private CategoriesRepository $categoriesRepository;
    private ?ProductsRepository $productsRepository = null;
    private CategoriesService $categoriesService;
    private CartRepository $cartRepository;
    private ?ProductItemsService $productItemsService = null;
    private CartService $cartService;
    private $result;
    private JSONView $jsonView;


    public function __construct()
    {
        //initializing database connection
        $this->productDatabase = new PDO("mysql:host=localhost;dbname=bb_uebung_3; charset=utf8", "root", "");
        //initializing repositories and services that need to be accessible from the start
        $this->productsRepository = new ProductsRepository($this->productDatabase);
        $this->categoriesService = new CategoriesService($this->productsRepository);
        $this->cartRepository = new CartRepository();

        //adding cart to session
        if (!isset($_SESSION['shopping_cart'])) {
            $_SESSION['shopping_cart'] = $this->cartRepository->createNewCart();
        }

        //TODO: cartService erst später initialisieren, wenn gebraucht
        //$this->cartService = new CartService($productsRepository, $_SESSION['shopping_cart']);

        $this->jsonView = new JSONView();
    }

    public function route(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->handleGetRequest();
                break;
            case 'POST':
                $this->handlePostRequest();
                break;
            case 'DELETE':
                $this->handleDeleteRequest();
                break;
            default:
                throw new InvalidArgumentException("Unsupported HTTP request method");
        }
    }


    private function handleGetRequest(): void
    {
        try {
            if (!isset($_GET["resource"])) {
                throw new \InvalidArgumentException("Invalid resource parameter");
            }
            $resource = strtolower($_GET["resource"]);
            switch ($resource) {
                case "types":
                    $this->result = $this->categoriesService->provideCategoryResult();
                    break;
                case "products":
                    //variable filter-type gets only initialized if necessary
                    $filterType = filter_var($_GET['filter-type'], FILTER_VALIDATE_INT) ?? null;
                    if ($filterType === false) {
                        throw new \InvalidArgumentException("Invalid filter-type parameter");
                    }

                    //initializing productItemsService only when needed
                    $this->productItemsService = new ProductItemsService($filterType, $this->productsRepository);
                    $this->result = $this->productItemsService->provideItemsResult();
                    break;
                case "cart":
                    //TODO: $_GET fuer cart implementieren
                    //$this->result = $this->cartService->getCartContents();
                    //break;
                default:
                    throw new InvalidArgumentException("Invalid value for resource parameter");
            }
            $this->jsonView->output($this->result);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            $this->jsonView->output(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            $this->jsonView->output(['error' => 'An unexpected error occurred']);
        }
    }

    private function handlePostRequest(): void
    {

    }

    private function handleDeleteRequest(): void
    {

    }
}