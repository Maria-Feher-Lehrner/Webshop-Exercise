<?php

use Fhtechnikum\Webshop\ProductDbController;

require 'vendor/autoload.php';

session_start();
$_SESSION["name"] = "test";
$_SESSION["id"] = "1";
$app = new ProductDbController();
$app->route();


/*
 * Session ist eigentlich eine Datenquelle und gehört hinter das Repo
 * Repo hat Funktionen zum Manipulieren der Ressourcen (lesen, schreiben, löschen).
 * Aber in der Regel nicht mit globalen Variablen, sondern mit Funktionen (bis auf das holen eines einzelnen Produkts runter)
 *
 *
 * Library sessionHandler => Idee ist, das als Gateway zur Session zu verwenden.
 * Erkenntnis: Session ist auch eine Datenquelle.
 * Session start wäre dann im Session Repo?
 *
 *
 * Interface: wird in Musterlösung EINGESETZT, indem es in der Serviceklasse explizit als Property übergeben wird.
 * Dh. die erwartet dann einfach das Interface und dessen Funktionen. Was dahinter liegt an Repos ist für die Service-Klasse
 * dann irrelevant.
 *
 *
 * TODO: im DTO zum Preis noch das total vom cartItem hinzufügen, damit das nicht im Frontend passiert.
 * Und dann wahrscheinlich auch noch das total vom cart?
 * */