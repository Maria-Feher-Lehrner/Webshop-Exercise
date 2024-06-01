<?php

namespace Fhtechnikum\Webshop\DTOs;

class CategoryDTO
{
    public string $productType;
    public string $url;
    public static function map($categoryModel): CategoryDTO{
    $categoryDTO = new CategoryDTO();
    $categoryDTO->productType = $categoryModel->name;
    $categoryDTO->url = "http://localhost/bb/Webshop/api/index.php?resource=products&filter-type=" . $categoryModel->id;
    return $categoryDTO;
    }
}