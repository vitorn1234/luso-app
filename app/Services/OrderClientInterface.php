<?php

// app/Services/OrderService.php
namespace App\Services;

use App\Domain\Order;

interface OrderClientInterface
{
    public function createOrder(Order $order): array;
}
