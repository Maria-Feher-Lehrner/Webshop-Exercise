<?php
require 'vendor/autoload.php';

//TODO: Header nach Loesen der CORS Probleme wieder rausnehmen!!!
header("Access-Control-Allow-Origin: *");
$app = new \Fhtechnikum\Webshop\ProductDbController();
$app->route();