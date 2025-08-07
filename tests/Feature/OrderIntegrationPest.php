<?php

use App\Domain\Item;
use App\Domain\Money;
use App\Domain\Order;
use App\Domain\TaxId;
use App\DTO\OrderRequestFactory;
use App\DTO\OrderRequestV1;
use App\DTO\OrderResponseFactory;
use App\DTO\OrderResponseV1;
use App\Http\Controllers\OrderController2;
use App\Services\OrderClientFactory;
use App\Services\OrderClientInterface;
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

    $this->requestV1 = [
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


    Mockery::mock(OrderClientFactory::class, function ($mock) use ($responseV1) {
        $mock->shouldReceive('make')
            ->with('v1') // Or whatever version you are testing
            ->andReturn(Mockery::mock(OrderClientInterface::class, function ($mock) use ($responseV1) {
                // Mock the createOrder method
                $mock->shouldReceive('createOrder')
                    ->with(Mockery::type(Order::class))
                    ->andReturn($responseV1);
                return $mock;
            }));
    });
    // Mock OrderClient
//    $mockClient = Mockery::mock(V1OrderClient::class);
////    $mockClient->shouldReceive('createOrder')->once()->andReturn($responseV1);
//
//    // Mock static factory method for OrderClientFactory
//    Mockery::mock(OrderClientFactory::class)
//        ->shouldReceive('make')
//        ->once()
//        ->with($version)
//        ->andReturn($mockClient);
//    $money = new Money($dto->total, $dto->currency);
//    $taxId = new TaxId($dto->customer_nif);
//    $items = array_map(fn($item) => new Item(
//        $item['sku'],
//        $item['qty'],
//        new Money($item['unit_price'], $dto->currency)
//    ), $dto->items);
//
//    $order=  new Order($dto->customer_name, $taxId, $money, $items);

    // Correct: Mock the returned client
//    $mockClient = Mockery::mock(V1OrderClient::class); // Assuming OrderClient is the actual class
//    $mockClient->shouldReceive('createOrder')->once()->andReturn($responseV1);
//    $mockClientFactory = Mockery::mock(OrderClientFactory::class);
//    $mockClientFactory->shouldReceive('make')->with('v1')->andReturn($mockClient);
//    $client = $mockClientFactory->make('v1');
    ///$result = $client->createOrder($order);

    // Mock OrderRequestFactory // return Order object
//    $mockOrderRequest = OrderRequestV1::fromArray($requestV1);
//    Mockery::mock(OrderRequestFactory::class)
//        ->shouldReceive('make')
//        ->once()
//        ->with($version, $requestV1)
//        ->andReturn($mockOrderRequest);

    // Mock OrderResponseFactory
//    Mockery::mock(OrderResponseFactory::class)
//        ->shouldReceive('make')
//        ->once()
//        ->with($version, $responseV1)
//        ->andReturn(OrderResponseV1::fromArray($responseV1));

//    Mockery::mock(OrderController2::class)->shouldAllowMockingProtectedMethods()
//        ->shouldReceive('buildOrderFromV1')
//        ->once()
//        ->with($mockOrderRequest)
//        ->andReturn($mockOrderResponse);
    // Instantiate controller
    $controller = new OrderController2();

    // Mock the buildOrderFromV1 method
    $controller = Mockery::spy($controller);
//    $controller->shouldAllowMockingProtectedMethods()
//        ->shouldReceive('buildOrderFromV1')
//        ->once()
//        ->with($mockOrderRequest)
//        ->andReturn();

//    Mockery::mock('alias:OrderClientFactory')
//        ->shouldReceive('createOrder')
//        ->once()
//        ->with()
//        ->andReturn();
    // Act
    $response = $controller->orderIntegration($requestV1, $version);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    $responseData = $response->getData();

    expect($responseData)->toMatchObject([
        "name" => "João Almeida",
        //"uuid"=> "26bdadc4-b2ee-4e83-b5f3-1b7da8d59a93",
        "number" => "ORD-1234-12345",
        "status" => "created",
        "createdAt" => "2025-07-22T14:12:09Z",
    ]);
});

it('handles exception and returns error response', function () {
    $version = 'v1';

    $controller = new OrderController2();
    $response = $controller->orderIntegration([], $version);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toEqual(400);
    expect($response->content())->toEqual('{"error":"error","message":"Invalid or missing `customer_name` in data"}');
    // Additional assertions about the error message if needed
});
