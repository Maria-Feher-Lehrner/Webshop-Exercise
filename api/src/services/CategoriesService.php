<?php

namespace Fhtechnikum\Webshop\services;

use Fhtechnikum\Webshop\DTOs;
use Fhtechnikum\Webshop\DTOs\CategoryDTO;
use Fhtechnikum\Webshop\repos\CategoriesRepository;

class CategoriesService
{
    private array $categoryList;

    public function __construct(CategoriesRepository $repository){
        $this->categoryList = $repository->getAllCategories();
    }

    /**
     * @return array|DTOs\CategoryDTO[]
     */
    public function provideCategoryResult(): array{
        $DTOList = [];

        foreach($this->categoryList as $category){
            $resultDTO = new CategoryDTO();
            $resultDTO->productType = $category['name'];
            $resultDTO->url = "http://localhost/bb/Webshop/api/index.php?resource=products&filter-type=".$category['id'];
            $DTOList[] = $resultDTO;
        }

        return $DTOList;
    }
}