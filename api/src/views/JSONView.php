<?php

namespace Fhtechnikum\Webshop\views;

class JSONView
{
    /**
     * @param mixed $data
     */
    public function output($data): void
    {
        header("Content-type: application/json");
        echo json_encode($data);
    }
}