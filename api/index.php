<?php
require 'vendor/autoload.php';

//TODO: Header nach Loesen der CORS Probleme wieder rausnehmen!!!
//header("Access-Control-Allow-Origin: *");
//ACHTUNG!: Zum Testen im Browser auch die index.html über localhost ansprechen und nicht einfach nur im Browser oeffnen!
$app = new \Fhtechnikum\Webshop\ProductDbController();
$app->route();