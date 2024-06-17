<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\DTOs\CartDTO;
use Fhtechnikum\Webshop\repos\ProductsRepository;
use Fhtechnikum\Webshop\services\CartService;
use Fhtechnikum\Webshop\views\JSONView;
use InvalidArgumentException;
use PDO;

class CartController implements ControllerInterface
{
    private PDO $productDatabase;
    private ProductsRepository $productsRepository;
    private CartService $cartService;
    private JSONView $jsonView;


    public function __construct($database)
    {
        $this->productDatabase = $database;
        $this->productsRepository = new ProductsRepository($this->productDatabase);

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
                $this->getCart();
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

    private function getCart(): void
    {
        $this->getCartContent($this->cartService);
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
    private function getRequestParameter($parameter, $filter, $options = null)
    {
        $value = filter_input(INPUT_GET, $parameter, $filter, $options);
        if ($value === null || $value === false) {
            throw new InvalidArgumentException("Invalid or missing $parameter parameter");
        }
        return $value;
    }
}