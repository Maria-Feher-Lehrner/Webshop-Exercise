<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\DTOs;
use Fhtechnikum\Webshop\models;
use Fhtechnikum\Webshop\models\CategoryModel;
use Fhtechnikum\Webshop\DTOs\CategoryDTO;
use Fhtechnikum\Webshop\repos\ProductsRepository;

class CategoriesService
{
    private array $categoryList;

    public function __construct(ProductsRepository $repository){
        $this->categoryList = $repository->getAllCategories();
    }

    /**
     * @return array|models\CategoryModel[]
     */
    public function mapAndProvideCategoryResult(): array{
        $categoryModelList = [];

        foreach($this->categoryList as $category){
            $CategoryModel = new CategoryModel();
            $CategoryModel->name = $category['name'];
            $CategoryModel->id = $category['id'];
            $categoryModelList[] = $CategoryModel;
        }

        return $categoryModelList;
    }
}