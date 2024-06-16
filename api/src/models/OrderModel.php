<?php

namespace Fhtechnikum\Webshop\models;

class OrderModel
{
    public int $id;
    public int $customer_id;
    public string $date;
    public float $total;
}