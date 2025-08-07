<?php

use App\DTO\OrderRequestV1;
use App\DTO\OrderResponseV1;
use App\Http\Controllers\OrderController2;
use App\Services\V1OrderClient;
use Illuminate\Http\JsonResponse;

beforeEach(function () {
    $this->responseV1 = ["data" =>
        [
            "uuid" => "e3d4b1d2-97db-4e5d-a7f5-7c9f7b1c2e10",
            "number" => "ORD-2025-00001",
            "status" => "created",
            "total" => "199.90",
            "currency" => "EUR",
            "created_at" => "2025-07-22T14:12:09Z"
        ]
    ];

    $this->responseV2 = [
        "links" => [
            "self" => "https://micros.services/api/v2/order/ORD-2025-00002"
        ],
        "data" => [
            "uuid" => "a1b2c3d4-e5f6-7g8h-9i0j-k1l2m3n4o5p7",
            "number" => "ORD-2025-00002",
            "status" => "shipped",
            "total" => "299.99",
            "currency" => "USD",
            "created_at" => "2025-07-23T10:30:00Z"
        ]
    ];

    $this->requestV1= [
        "customer_name" => "João Almeida",
        "customer_nif" => "123456789",
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

    $this->requestV2 = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => [
                    "name" => "João Almeida",
                    "nif" => "123456789"
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
});

it('successfully processes an order V1', function () {
    // Arrange
    $responseV1 = $this->responseV1;
    $requestV1 = $this->requestV1;
    $version = 'v1';

    // Mock OrderClient
    $mockClient = Mockery::mock(V1OrderClient::class);
    $mockResponse = $responseV1;
    $mockClient->shouldReceive('createOrder')->once()->andReturn($mockResponse);

    // Mock static factory method for OrderClientFactory
    Mockery::mock('alias:OrderClientFactory')
        ->shouldReceive('make')
        ->once()
        ->with($version)
        ->andReturn($mockClient);

    // Mock OrderRequestFactory // return Order object
    $mockOrderRequest = OrderRequestV1::fromArray($requestV1);
    Mockery::mock('alias:OrderRequestFactory')
        ->shouldReceive('make')
        ->once()
        ->with($version, $requestV1)
        ->andReturn($mockOrderRequest);

    // Mock OrderRequestFactory
    $mockOrderRequest = OrderRequestV1::fromArray($requestV1);
    Mockery::mock('alias:OrderRequestFactory')
        ->shouldReceive('make')
        ->once()
        ->with($version, $requestV1)
        ->andReturn($mockOrderRequest);

    // Instantiate controller
    $controller = new OrderController2();

    // Mock the buildOrderFromV1 method
    $controller = Mockery::spy($controller);
    $controller->shouldAllowMockingProtectedMethods()
        ->shouldReceive('buildOrderFromV1')
        ->once()
        ->with($mockOrderRequest)
        ->andReturn($responseV1);

    // Act
    $response = $controller->orderIntegration($responseV1, $version);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    $responseData = $response->getData();
    dump($responseData);
    expect($responseData)->toMatchObject([
        'uuid' => 'uuid-123',
        'number' => 'ORD-001',
        'createdAt' => '2023-10-04'
    ]);
});

it('handles exception and returns error response', function () {
    // Arrange
    $data = [];
    $version = 'v1';

    // Mock static factory to throw exception
    Mockery::mock('alias:OrderClientFactory')
        ->shouldReceive('make')
        ->once()
        ->with($version)
        ->andThrow(new \Exception('Error'));

    $controller = new OrderController();

    // Act
    $response = $controller->orderIntegration($data, $version);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toEqual(500);
    // Additional assertions about the error message if needed
});
