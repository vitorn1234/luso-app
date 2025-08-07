<?php

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\Money;
use App\Domain\Order;
use App\Domain\TaxId;
use App\Domain\User;
use App\DTO\OrderRequestFactory;
use App\DTO\OrderRequestV1;
use App\DTO\OrderRequestV2;
use App\Helper\AppHelper;
use App\Service\OrderClientFactory;
use App\DTO\OrderResponseFactory;
use Illuminate\Http\Request;


class OrderController2 extends Controller
{
    public array $data = [
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

    public array $data2 = [
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
    public function orderIntegration(/*Request $request*/ array $data = [], string $version = 'v2'): \Illuminate\Http\JsonResponse
    {
        $data = $this->data2;
        try {
            // create client for selected version
            $client = OrderClientFactory::make($version);
            $versionCapitalized = strtoupper($version);

            //$data = $request->json()->all();
            //process request data
            $dtoRequest = OrderRequestFactory::make($version, $data);

            // Dynamically construct the method name TODO improve
            $methodName = 'buildOrderFrom' . $versionCapitalized;
            $order = $this->$methodName($dtoRequest);

            //we can switch to another version if we add any variation
            $response = $client->createOrder($order);

            $dtoResponse = OrderResponseFactory::make($version, $response);

            $order->uuid = $dtoResponse->uuid;
            $order->number = $dtoResponse->number;
            $order->createdAt = $dtoResponse->created_at;
        } catch (\Exception $e) {
            return AppHelper::getResponseError($version, $e);
        }

        return response()->json($order);
    }

    protected function buildOrderFromV1(OrderRequestV1 $dto): Order
    {
        $money = new Money($dto->total, $dto->currency);
        $taxId = new TaxId($dto->customer_nif);
        $items = array_map(fn($item) => new Item(
            $item['sku'],
            $item['qty'],
            new Money($item['unit_price'], $dto->currency)
        ), $dto->items);

        return new Order($dto->customer_name, $taxId, $money, $items);
    }

    protected function buildOrderFromV2(OrderRequestV2 $dto): Order
    {
        $attributes = $dto->attributes;
        $customer = $attributes['customer'];
        $summary = $attributes['summary'];
        $lines = $attributes['lines'];

        $money = new Money($summary['total'], $summary['currency']);
        $taxId = new TaxId($customer['nif']);
        $items = array_map(fn($line) => new Item(
            $line['sku'],
            $line['qty'],
            new Money($line['price'], $summary['currency'])
        ), $lines);

        return new Order($customer['name'], $taxId, $money, $items);
    }

    public function returnError()
    {
        $message = "Line/Item at index 1: 'sku' must be a string.";
        $version = 1; // or 2

        if ($version == 1) {
            // Remove "Line"
            $cleaned_message = preg_replace('/Line/', '', $message);
        } elseif ($version == 2) {
            // Remove "Item"
            $cleaned_message = preg_replace('/Item/', '', $message);
        } else {
            // No change for other versions
            $cleaned_message = $message;
        }

        echo $cleaned_message;
    }
}
