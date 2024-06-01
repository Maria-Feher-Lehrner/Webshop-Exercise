<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\DTOs\CartDTO;
use Fhtechnikum\Webshop\DTOs\CategoryDTO;
use Fhtechnikum\Webshop\DTOs\ProductDTO;
use Fhtechnikum\Webshop\DTOs\ProductListDTO;
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
    private ProductsRepository $productsRepository;
    private CategoriesService $categoriesService;
    private ?ProductItemsService $productItemsService = null;
    private CartService $cartService;
    private JSONView $jsonView;


    public function __construct()
    {
        //initializing database connection
        $this->productDatabase = new PDO("mysql:host=localhost;dbname=bb_uebung_3; charset=utf8", "root", "");

        //initializing repositories and services that need to be accessible from the start
        $this->productsRepository = new ProductsRepository($this->productDatabase);
        $this->categoriesService = new CategoriesService($this->productsRepository);

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
        $resource = $this->getRequestParameter('resource', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        switch (strtolower($resource)) {
            case "types":
                $this->buildCategoryList();
                break;
            case "products":
                //variable filter-type gets only initialized if necessary
                $filterType = $this->getRequestParameter('filter-type', FILTER_VALIDATE_INT);

                //initializing productItemsService only when needed
                $this->productItemsService = new ProductItemsService($filterType, $this->productsRepository);
                $this->buildProductsList($this->productItemsService);
                break;
            case "cart":
                $this->buildCartContent($this->cartService);
                break;
            default:
                throw new InvalidArgumentException("Invalid value for resource parameter");
        }
    }
    private function getRequestParameter($parameter, $filter, $options = null)
    {
        $value = filter_input(INPUT_GET, $parameter, $filter, $options);
        if ($value === null || $value === false) {
            throw new InvalidArgumentException("Invalid or missing $parameter parameter");
        }
        return $value;
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

    private function handlePostRequest(): void
    {
        try {
            $productId = $this->getArticleIdParameter();
            $this->cartService->addProductToCart($productId);
            http_response_code(200);
            $this->jsonView->output(['state' => 'OK']);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            $this->jsonView->output(['state' => 'ERROR', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            $this->jsonView->output(['state' => 'ERROR']);
        }

    }

    private function handleDeleteRequest(): void
    {
        try {
            $productId = $this->getArticleIdParameter();

            $this->cartService->removeProduct($productId);
            http_response_code(200);
            $this->jsonView->output(['state' => 'OK']);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            $this->jsonView->output(['state' => 'ERROR', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            $this->jsonView->output(['state' => 'ERROR']);
        }
    }

    private function getArticleIdParameter()
    {
        $resource = $this->getRequestParameter('resource', FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '/^[a-zA-Z]+$/'],
        ]);
        $resource = htmlspecialchars($resource, ENT_QUOTES, 'UTF-8');
        $productId = $this->getRequestParameter('articleId', FILTER_VALIDATE_INT);

        if ($resource !== "cart" || $productId === false) {
            throw new \InvalidArgumentException("Invalid parameters");
        }
        return $productId;
    }
}