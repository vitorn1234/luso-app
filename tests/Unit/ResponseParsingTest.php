<?php

use App\Domain\Item;
use App\Domain\Money;
use App\Domain\Order;
use App\Domain\TaxId;
use App\Domain\User;
use App\Http\Resources\OrdersResource;

// repetitive use but might be needed if tested alone
$validatedData = [
    "customer_name" => "João Almeida",
    "customer_nif" => "12345678Z",
    "total" => "115.00",
    "currency" => "EUR",
    "items" => [
        [
            "sku" => "PEN-16GB",
            "qty" => 3,
            "unit_price" => "5.00",
        ],
        [
            "sku" => "NOTE-A5",
            "qty" => 10,
            "unit_price" => "10.00",
        ],
    ],
];

$taxId = new TaxId($validatedData['customer_nif']);
$user = new User($validatedData['customer_name'], $taxId);
$money = new Money($validatedData['total'], $validatedData['currency']);
$items = array();
foreach ($validatedData['items'] as $itemData) {
    $items[] = new Item($itemData['sku'], $itemData['qty'], $itemData['unit_price']);
}
$order = new Order($user, $money, $items);

$responseArrayV1 = [
    'data' => [
        'uuid' => $order->getUuid(),
        'number' => $order->getNumber(),
        'status' => 'created',
        'total' => $order->getMoney()->amount(),
        'currency' => 'EUR',
        'created_at' => $order->getCreatedAt(),
    ]
];
it('v1 response parsing test', function ($order, $responseArrayV1) {
    $apiVersion = "v1";

    $this->assertEqualsCanonicalizing($responseArrayV1, (new OrdersResource($apiVersion, $order))->toArray());
})->with([
    [
        $order, // your Order object
        $responseArrayV1 // expected response array
    ],
]);

$responseArrayV2 = [
    "links" => [
        "self" => "https://micros.services/api/v2/order/".$order->getNumber()
    ],
    "data" => [
        "type" => "orders",
        "id" => $order->getNumber(),
        "attributes" => [
            "uuid" => $order->getUuid(),
            "status" => "created",
            "currency" => $order->getMoney()->currency(),
            "total" => $order->getMoney()->amount(),
            "created_at" => $order->getCreatedAt(),
        ]
    ]
];

it('v2 response parsing test', function ($order, $responseArrayV2) {
    $apiVersion = "v2";

    $this->assertEqualsCanonicalizing($responseArrayV2, (new OrdersResource($apiVersion, $order))->toArray());
})->with([
    [
        $order, // your Order object
        $responseArrayV2 // expected response array
    ],
]);
