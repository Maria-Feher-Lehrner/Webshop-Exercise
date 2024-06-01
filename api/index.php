<?php

use Fhtechnikum\Webshop\ProductDbController;

require 'vendor/autoload.php';

session_start();
$_SESSION["name"] = "test";
$_SESSION["id"] = "1";
$app = new ProductDbController();
$app->route();
