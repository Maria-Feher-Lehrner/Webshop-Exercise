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

class ProductDbController implements ControllerInterface
{
    //TODO: Controller besser in 2 versch. Controller aufteilen, weil zwei verschiedene Zuständigkeiten gehandelt werden.
    // Repo kann aber gemeinsam genutzt werden.
    // Klassiker dafür ist auch ein eigenes Login - nie mit den anderen Ressourcen vermischen.


    private PDO $productDatabase;
    private ProductsRepository $productsRepository;
    private CategoriesService $categoriesService;
    private ?ProductItemsService $productItemsService = null;
    private CartService $cartService;
    private JSONView $jsonView;


    public function __construct($database)
    {
        $this->productDatabase = $database;

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
                $this->getProductListsOrCart();
                break;
            case 'POST':
                $this->postProductsToCart();
                break;
            case 'DELETE':
                $this->removeProductsFromCart();
                break;
            default:
                throw new InvalidArgumentException("Unsupported HTTP request method");
        }
    }

    //ACHTUNG! Hier zwar laut Angabe die Verben POST und DELETE eingesetzt, aber eigentlich nicht nach Standard umgesetzt (das wäre besser ein put)


    private function getProductListsOrCart(): void
    {
        $resource = $this->getRequestParameter('resource', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        switch (strtolower($resource)) {
            case "types":
                $this->getCategoryList();
                break;
            case "products":
                //variable filter-type gets only initialized if necessary
                $filterType = $this->getRequestParameter('filter-type', FILTER_VALIDATE_INT);

                //initializing productItemsService only when needed
                $this->productItemsService = new ProductItemsService($filterType, $this->productsRepository);
                $this->getProductsList($this->productItemsService);
                break;
            case "cart":
                $this->getCartContent($this->cartService);
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

    private function getCategoryList(): void
    {
        $categoryList = $this->categoriesService->mapAndProvideCategoryResult();
        $dtoList = [];
        foreach ($categoryList as $item) {
            $dtoList[] = CategoryDTO::map($item);
        }
        $this->jsonView->output($dtoList);
    }

    private function getProductsList($service): void
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

    private function getCartContent(CartService $cartService): void
    {
        $shoppingCart = $cartService->getShoppingCart();
        $cartDTO = CartDTO::map($shoppingCart);
        $this->jsonView->output($cartDTO);
    }

    private function postProductsToCart(): void
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

    private function removeProductsFromCart(): void
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