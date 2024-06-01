<?php
require 'vendor/autoload.php';

session_start();
$_SESSION["name"] = "test";
$_SESSION["id"] = "1";
$app = new \Fhtechnikum\Webshop\ProductDbController();
$app->route();

/*
$pdo = new PDO("mysql:host=localhost;dbname=bb_uebung_3; charset=utf8", "root", "");
$repo = new \Fhtechnikum\Webshop\repos\ProductsRepository($pdo);
$productList = $repo->getAllProducts();
$productModelList = $repo->getMappedProducts($productList);
print_r($productModelList);*/