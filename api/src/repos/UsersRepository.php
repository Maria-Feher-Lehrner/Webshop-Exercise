<?php

namespace Fhtechnikum\Webshop\repos;

use Fhtechnikum\Webshop\models\OrderModel;
use Fhtechnikum\Webshop\models\UserModel;
use PDO;

class UsersRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function getUserByEmail($email): ?UserModel
    {
        $query = "SELECT email as userName, password as passwordHash, id as userId FROM customers
                    WHERE email = :email";
        $statement = $this->database->prepare($query);
        $statement->bindParam(":email", $email);
        $statement->execute();
        $userModel = $statement->fetchObject(UserModel::class);

        return $userModel ?: null;
    }

    public function createOrderEntryAndReturnOrderId(?int $customerId, float $totalPrice): int
    {
        $query = "INSERT INTO orders (customer_id, total, date)
                    VALUES (:customer_id, :total, NOW())";
        $statement = $this->database->prepare($query);

        if ($customerId === null) {
            $statement->bindValue(':customer_id', null, PDO::PARAM_NULL);
        } else {
            $statement->bindParam(':customer_id', $customerId);
        }

        $statement->bindParam(':total', $totalPrice);
        $statement->execute();

        /*if (!$stmt->execute()) {
            throw new Exception("Failed to create order");
        }*/

        //TODO: Nachdenken, wie man diese zwei Zuständigkeiten trennen könnte (return OrderId), ohne dass Möglichkeit besteht,
        // dass inzwischen potenziell eine andere Order bearbeitet und in die DB geslottet wird?
        return (int)$this->database->lastInsertId();
    }

    public function createEntryOrderPosition(int $orderId, int $articleId, int $amount, float $itemTotal): void
    {
        $query = "INSERT INTO order_positions (order_id, product_id, amount, item_total) 
                    VALUES (:order_id, :article_id, :amount, :item_total)";
        $statement = $this->database->prepare($query);
        $statement->bindParam(':order_id', $orderId);
        $statement->bindParam(':article_id', $articleId);
        $statement->bindParam(':amount', $amount);
        $statement->bindParam(':item_total', $itemTotal);
        $statement->execute();

        /*if (!$stmt->execute()) {
            throw new Exception("Failed to create order");
        }*/
    }

    public function getUserCartHistory(int $customerId): array {
        $query = "SELECT * FROM orders
                    WHERE customer_id = :id";
        $statement = $this->database->prepare($query);
        $statement->bindParam(':id', $customerId, PDO::PARAM_INT);
        $statement->execute();
        $orderList = $statement->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->getMappedOrders($orderList);
        return $result;
    }

    public function getMappedOrders($orderList): array
    {
        $mappedOrders = [];
        foreach ($orderList as $order) {
            $orderModel = new OrderModel();
            $orderModel->id = $order["id"];
            $orderModel->customerId = $order["customer_id"];
            $orderModel->date = $order["date"];
            $orderModel->total = $order["total"];

            $mappedOrders[] = $orderModel;
        }
        return $orderList;
    }
}