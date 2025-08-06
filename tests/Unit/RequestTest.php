<?php

// repetitive use but might be needed if tested alone
use App\Domain\Order;
use App\Service\Service;

$data = [
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
$jsonDataV2 = [
    "data" => [
        "type" => "orders",
        "attributes" => [
            "customer" => [
                "name" => "João Almeida",
                "nif" => "12345678Z"
            ],
            "summary" => [
                "currency" => "EUR",
                "total" => "115.00"
            ],
            "lines" => [
                [
                    "sku" => "PEN-16GB",
                    "qty" => 3,
                    "price" => "5.00"
                ],
                [
                    "sku" => "NOTE-A5",
                    "qty" => 10,
                    "price" => "10.00"
                ]
            ]
        ]
    ]
];
it('v1 validating creation of order test', function ($data) {
    $apiVersion = "v1";
    $service = new Service($apiVersion, $data);
    $order = $service->createOrder()->getOrder();
    // Assert
    $this->assertInstanceOf(Order::class, $order);
    $this->assertEquals($data['customer_name'], $order->name);
    $this->assertEquals($data['customer_nif'], $order->getTaxId()->taxId);
    $this->assertEquals($data["total"], $order->getMoney()->amount());
    $this->assertCount(2, $order->getItems());
    $this->assertEquals($data["items"][0]['sku'], ($order->getItems()[0])->sku);
    $this->assertEquals($data["items"][0]['unit_price'], ($order->getItems()[0])->getMoney()->amount());
//    $this->assertStringStartsWith('ORD-', $order->number);
//    $this->assertNotEmpty($order->uuid);
//    $this->assertStringContainsString('Z', $order->createdAt); // ISO date

})->with([[$data],]);

it('v2 validating creation of order test', function ($data) {
    $apiVersion = "v2";
    $service = new Service($apiVersion, $data);
    $order = $service->createOrder()->getOrder();
    // Assert
    $this->assertInstanceOf(Order::class, $order);
    $this->assertEquals($data['data']['attributes']['customer']['name'], $order->name);
    $this->assertEquals($data['data']['attributes']['customer']['nif'], $order->getTaxId()->taxId);
    $this->assertEquals($data['data']['attributes']['summary']['total'], $order->getMoney()->amount());
    $this->assertCount(2, $order->getItems());
    $this->assertEquals($data['data']['attributes']["lines"][0]['sku'], ($order->getItems()[0])->sku);
    $this->assertEquals($data['data']['attributes']["lines"][1]['price'], ($order->getItems()[1])->getMoney()->amount());
//    $this->assertStringStartsWith('ORD-', $order->number);
//    $this->assertNotEmpty($order->uuid);
//    $this->assertStringContainsString('Z', $order->createdAt); // ISO date

})->with([[$jsonDataV2],]);
