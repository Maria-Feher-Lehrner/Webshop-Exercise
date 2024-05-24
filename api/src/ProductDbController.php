<?php

namespace Fhtechnikum\Webshop;

use Fhtechnikum\Webshop\views\JSONView;
use http\Exception\InvalidArgumentException;
use PDO;

class ProductDbController
{
    private PDO $productDatabase;
    private CategoriesRepository $categoriesRepository;
    private ?ProductsRepository $productsRepository = null;
    private CategoriesService $categoriesService;
    private ?ProductItemsService $productItemsService = null;
    private $result;
    private JSONView $jsonView;


    public function __construct()
    {
        //initializing database connection
        $this->productDatabase = new PDO("mysql:host=localhost;dbname=bb_uebung_3; charset=utf8", "root", "");

        //first initializing only categories-respository and -service in constructor - remaining repository/service only if necessary for
        //additional parameters ('products' & 'filter-type')
        $this->initializingCategories($this->productDatabase);

        $this->jsonView = new JSONView();
    }

    public function route(): void
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
                    //initializing remaining repository and service for case 'products' only if necessary
                    $this->initializingProducts($this->productDatabase, $filterType);
                    $this->result = $this->productItemsService->provideItemsResult();
                    break;
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

    private function initializingCategories(PDO $productDatabase): void {
        $this->categoriesRepository = new CategoriesRepository($this->productDatabase);
        $this->categoriesService = new CategoriesService($this->categoriesRepository);
    }

    private function initializingProducts(PDO $productDatabase, int $filterType): void {
        $this->productsRepository = new ProductsRepository($this->productDatabase, $filterType);
        $this->productItemsService = new ProductItemsService($this->productsRepository);
    }
}