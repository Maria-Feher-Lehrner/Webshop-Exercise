<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\repos\ProductsRepository;

class ProductItemsService
{
    private int $typeId;
    private array $productList;
    private ProductsRepository $productsRepository;

    public function __construct(int $typeId, ProductsRepository $productsRepository)
    {
        $this->typeId = $typeId;
        $this->productsRepository = $productsRepository;
        $this->productList = $productsRepository->getProductsByTypeId($this->typeId);
    }

    /**
     * @return array|models\ProductModel[]
     */
    public function getProductModelList(): array
    {
        return $this->productsRepository->getMappedProducts($this->productList);
    }

    public function getCategoryInfo(): array
    {
        if (!empty($this->productList)) {
            return [
                'categoryName' => $this->productList[0]['categoryName'],
                'categoryId' => $this->productList[0]['categoryId']
            ];
        }
        return ['categoryName' => '', 'categoryId' => 0];
    }
}