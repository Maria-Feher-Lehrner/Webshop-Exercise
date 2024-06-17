<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\DTOs\CategoryDTO;
use Fhtechnikum\Webshop\DTOs\ProductDTO;
use Fhtechnikum\Webshop\DTOs\ProductListDTO;
use Fhtechnikum\Webshop\repos\ProductsRepository;
use Fhtechnikum\Webshop\services\CategoriesService;
use Fhtechnikum\Webshop\services\ProductItemsService;
use Fhtechnikum\Webshop\views\JSONView;
use http\Exception\InvalidArgumentException;
use PDO;

class ProductDbController implements ControllerInterface
{
    private PDO $productDatabase;
    private ProductsRepository $productsRepository;
    private CategoriesService $categoriesService;
    private ?ProductItemsService $productItemsService = null;
    private JSONView $jsonView;


    public function __construct($database)
    {
        $this->productDatabase = $database;

        $this->productsRepository = new ProductsRepository($this->productDatabase);
        $this->categoriesService = new CategoriesService($this->productsRepository);

        $this->jsonView = new JSONView();
    }

    public function route(): void
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