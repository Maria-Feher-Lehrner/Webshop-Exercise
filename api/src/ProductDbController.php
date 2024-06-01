<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\DTOs\CartDTO;
use Fhtechnikum\Webshop\DTOs\CategoryDTO;
use Fhtechnikum\Webshop\DTOs\ProductDTO;
use Fhtechnikum\Webshop\DTOs\ProductListDTO;
//use Fhtechnikum\Webshop\repos\CartRepository;
//use Fhtechnikum\Webshop\repos\CategoriesRepository;
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
    //private CartRepository $cartRepository;
    private ?ProductItemsService $productItemsService = null;
    private CartService $cartService;
    //TODO code umbauen und result entfernen
    private $result;
    private JSONView $jsonView;


    public function __construct()
    {
        //initializing database connection
        $this->productDatabase = new PDO("mysql:host=localhost;dbname=bb_uebung_3; charset=utf8", "root", "");
        //initializing repositories and services that need to be accessible from the start
        $this->productsRepository = new ProductsRepository($this->productDatabase);
        $this->categoriesService = new CategoriesService($this->productsRepository);
        //$this->cartRepository = new CartRepository($this->productDatabase);

        //Checking if there is already a shopping cart in running session. If not: adding cart to session
        if (!isset($_SESSION['shopping_cart'])) {
            $_SESSION['shopping_cart'] = $this->productsRepository->createNewCart();
        }
        //initializing CartService after shopping cart is added to session
        $this->cartService = new CartService($this->productsRepository, $_SESSION['shopping_cart']);

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
                    $this->buildCartContent($this->cartService);
                    break;
                default:
                    throw new InvalidArgumentException("Invalid value for resource parameter");
            }
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
        try {
            if (!isset($_GET["resource"]) || !isset($_GET['articleId'])) {
                throw new \InvalidArgumentException("Missing required parameters");
            }

            $resource = strtolower($_GET['resource']);
            $productId = filter_var($_GET['articleId'], FILTER_VALIDATE_INT);

            if ($resource !== "cart" || $productId === false) {
                throw new \InvalidArgumentException("Invalid parameters");
            }

            $this->cartService->addProductToCart($productId);
            http_response_code(200);
            $this->jsonView->output(['state' => 'OK']);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            $this->jsonView->output(['state' => 'ERROR', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            $this->jsonView->output(['state' => 'ERROR', 'error' => 'An unexpected error occurred']);
        }

    }

    private function handleDeleteRequest(): void
    {
        try {
            if (!isset($_GET["resource"]) || !isset($_GET['articleId'])) {
                throw new \InvalidArgumentException("Missing required parameters");
            }

            $resource = strtolower($_GET['resource']);
            $productId = filter_var($_GET['articleId'], FILTER_VALIDATE_INT);

            if ($resource !== "cart" || $productId === false) {
                throw new \InvalidArgumentException("Invalid parameters");
            }

            $this->cartService->removeProduct($productId);
            http_response_code(200);
            $this->jsonView->output(['state' => 'OK']);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            $this->jsonView->output(['state' => 'ERROR', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            $this->jsonView->output(['state' => 'ERROR', 'error' => 'An unexpected error occurred']);
        }
    }

    private function buildCategoryList(): void
    {
        $categoryList = $this->categoriesService->mapAndProvideCategoryResult();
        $dtoList = [];
        foreach ($categoryList as $item) {
            $dtoList[] = CategoryDTO::map($item);
            //TODO: Interface!
        }
        $this->jsonView->output($dtoList);
    }

    private function buildProductsList($service): void
    {
        $productList = $service->getProductModelList();
        $dtoList = [];
        foreach ($productList as $item) {
            $dtoList[] = ProductDTO::map($item);
        }
        $productListDTO = $this->mapProductListDTO($productList, $dtoList, $service);
        $this->jsonView->output($productListDTO);
    }

    private function mapProductListDTO(array $productList, array $dtoList, $service): ProductListDTO
    {
        $categoryInfo = $service->getCategoryInfo();
        $productListDTO = new ProductListDTO();

        $productListDTO->categoryName = $categoryInfo['categoryName'];
        $productListDTO->categoryId = $categoryInfo['categoryId'];
        $productListDTO->products = $dtoList;
        $productListDTO->url = "http://localhost/bb/Webshop/api/index.php?resource=types";

        return $productListDTO;
    }

    private function buildCartContent(CartService $cartService): void
    {
        $shoppingCart = $cartService->getShoppingCart();
        $cartDTO = CartDTO::map($shoppingCart);
        $this->jsonView->output($cartDTO);
    }
}