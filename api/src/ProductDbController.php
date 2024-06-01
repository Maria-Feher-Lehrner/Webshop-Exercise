<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\DTOs\CategoryDTO;
use Fhtechnikum\Webshop\DTOs\ProductDTO;
use Fhtechnikum\Webshop\DTOs\ProductListDTO;
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
        $this->cartRepository = new CartRepository($this->productDatabase);

        //Checking if there is already a shopping cart in running session. If not: adding cart to session
        if (!isset($_SESSION['shopping_cart'])) {
            $_SESSION['shopping_cart'] = $this->cartRepository->createNewCart();
        }
        //initializing CartService after shopping cart is added to session
        $this->cartService = new CartService($this->cartRepository, $_SESSION['shopping_cart']);

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
                    $this->buildCategoryList();
                    break;
                case "products":
                    //variable filter-type gets only initialized if necessary
                    $filterType = filter_var($_GET['filter-type'], FILTER_VALIDATE_INT) ?? null;
                    //TODO: Verschachtelung aufloesen
                    if ($filterType === false) {
                        throw new \InvalidArgumentException("Invalid filter-type parameter");
                    }
                    //initializing productItemsService only when needed
                    $this->productItemsService = new ProductItemsService($filterType, $this->productsRepository);
                    //$this->result = $this->productItemsService->provideItemsResult();

                    $this->buildProductsList($this->productItemsService);
                    break;
                case "cart":
                    //TODO: $_GET fuer cart implementieren
                    //$this->result = $this->cartService->getCartContents();
                    //break;
                default:
                    throw new InvalidArgumentException("Invalid value for resource parameter");
            }
            //TODO: am ende noch einzelne functions so umbauen, dass jede eine jsonView auswirft, statt hier über result
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
        if (!isset($_GET["resource"]) || !isset($_GET['articleId'])) {
            throw new \InvalidArgumentException("Missing required parameters");
        }

        $resource = strtolower($_GET['resource']);
        $productId = filter_var($_GET['articleId'], FILTER_VALIDATE_INT);

        if ($resource !== "cart" || $productId === false) {
            throw new \InvalidArgumentException("Invalid parameters");

        }
        $this->cartService->addProductToCart($productId);
    }

    private function handleDeleteRequest(): void
    {

    }

    private function buildCategoryList(): void
    {
        $categoryList = $this->categoriesService->mapAndProvideCategoryResult();
        $dtoList = [];
        foreach ($categoryList as $item) {
            $dtoList[] = CategoryDTO::map($item);
            //TODO: Interface!
        }
        $this->result = $dtoList;
    }

    private function buildProductsList($service): void
    {
        $productList = $service->mapAndReturnItemsList();
        $dtoList = [];
        foreach ($productList as $item) {
            $dtoList[] = ProductDTO::map($item);
        }
        $productListDTO = $this->mapProductListDTO($productList, $dtoList);
        $this->result = $productListDTO;
    }

    private function mapProductListDTO(array $productList, array $dtoList): ProductListDTO
    {
        $productListDTO = new ProductListDTO();

        if (isset($productList[0])) {
            $productListDTO->categoryName = $productList[0]->categoryName;
            $productListDTO->categoryId = $productList[0]->categoryId;
        } else {
            $productListDTO->categoryName = "";
            $productListDTO->categoryId = 0;
        }

        $productListDTO->products = $dtoList;
        $productListDTO->url = "http://localhost/bb/Webshop/api/index.php?resource=types";

        return $productListDTO;
    }
}