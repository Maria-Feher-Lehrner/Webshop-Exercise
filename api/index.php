<?php
require 'vendor/autoload.php';

session_start();
$_SESSION["name"] = "test";
$_SESSION["id"] = "1";
$app = new \Fhtechnikum\Webshop\ProductDbController();
$app->route();