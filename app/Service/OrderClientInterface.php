<?php

// app/Service/OrderService.php
namespace App\Service;

use App\Domain\Order;
interface OrderClientInterface
{
    public function createOrder(Order $order): array;
}
