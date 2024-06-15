<?php

use Fhtechnikum\Webshop\App;

require 'vendor/autoload.php';

session_start();
$app = new App();
$app->start();


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
 * dann irrelevant.*/