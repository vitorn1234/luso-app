<?php

use App\Domain\Order;
use App\DTO\OrderRequestV2;
use App\Domain\Money;
use App\Domain\Item;

it('Order - calculate total - ', function () {
    $items = [
        new Item('sku1', 2, new Money('10.00', 'EUR')),
        new Item('sku2', 1, new Money('20.00', 'EUR')),
    ];

    $total = array_reduce($items, fn($carry , $item) => $carry + ((float)$item->getMoney()->amount() * $item->qty), 0);
    expect($total)->toEqual(40.00);
});

// tests/Feature/OrderApiTest.php
//it('creates order via v1 endpoint with HTTP fake', function () {
//    Http::fake([
//        'https://micros.services/api/v1/order' => Http::response(['data' => [/* ... */]], 200),
//    ]);
//
//    // Send request to your local route and assert
//});

it('parses a valid payload correctly', function () {
    $payload = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => [
                    "name" => "João Almeida",
                    "nif" => "504321123"
                ],
                "summary" => [
                    "currency" => "EUR",
                    "total" => "199.90"
                ],
                "lines" => [
                    ["sku" => "PEN-16GB", "qty" => 3, "price" => "9.90"],
                    ["sku" => "NOTE-A5", "qty" => 10, "price" => "12.00"]
                ]
            ]
        ]
    ];

    $dto = OrderRequestV2::fromArray($payload);
    expect($dto->type)->toBe('orders')
        ->and($dto->attributes['customer']['name'])->toBe('João Almeida')
        ->and($dto->attributes['customer']['nif'])->toBe('504321123')
        ->and($dto->attributes['summary']['currency'])->toBe('EUR');
});

it('throws exception if "data" key is missing', function () {
    $payload = [];
    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, 'Missing "data" key in payload.');

it('throws exception for invalid type', function () {
    $payload = [
        "data" => [
            "type" => "invalid_type",
            "attributes" => [
                "customer" => ["name" => "Name", "nif" => "123456789"],
                "summary" => ["currency" => "EUR", "total" => "100.00"],
                "lines" => [["sku" => "item", "qty" => 1, "price" => "10.00"]]
            ]
        ]
    ];

    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, 'Invalid or missing "type". Must be "orders".');

it('throws exception if customer is missing', function () {
    $payload = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "summary" => ["currency" => "EUR", "total" => "100.00"],
                "lines" => [["sku" => "item", "qty" => 1, "price" => "10.00"]]
            ]
        ]
    ];

    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, 'Missing or invalid "customer" in attributes.');

it('throws exception for invalid nif', function () {
    $payload = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => ["name" => "Name", "nif" => "123"],
                "summary" => ["currency" => "EUR", "total" => "100.00"],
                "lines" => [["sku" => "item", "qty" => 1, "price" => "10.00"]]
            ]
        ]
    ];

    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, 'Invalid Tax ID (NIF).');

it('throws exception if summary is missing', function () {
    $payload = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => ["name" => "Name", "nif" => "123456789"],
                "lines" => [["sku" => "item", "qty" => 1, "price" => "10.00"]]
            ]
        ]
    ];

    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, 'Missing or invalid "summary" in attributes.');

it('throws exception for invalid total format', function () {
    $payload = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => ["name" => "Name", "nif" => "123456789"],
                "summary" => ["currency" => "EUR", "total" => "abc"],
                "lines" => [["sku" => "item", "qty" => 1, "price" => "10.00"]]
            ]
        ]
    ];

    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, 'Total must be a valid decimal number with up to 2 decimal places.');

it('throws exception if lines array is empty', function () {
    $payload = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => ["name" => "Name", "nif" => "123456789"],
                "summary" => ["currency" => "EUR", "total" => "100.00"],
                "lines" => []
            ]
        ]
    ];

    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, 'Missing, invalid, or empty "lines" in attributes.');

it('throws exception for invalid line structure', function () {
    $payload = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => ["name" => "Name", "nif" => "123456789"],
                "summary" => ["currency" => "EUR", "total" => "100.00"],
                "lines" => [
                    ["sku" => "item", "qty" => "notInt", "price" => "10.00"]
                ]
            ]
        ]
    ];

    OrderRequestV2::fromArray($payload);
})->throws(InvalidArgumentException::class, "Line/Item at index 0: 'qty' must be a positive integer.");



