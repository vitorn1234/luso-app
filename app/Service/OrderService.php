<?php

// app/Services/OrderService.php
namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use App\Domain\Order;
use Illuminate\Support\Facades\Log;

interface OrderClientInterface
{
    public function createOrder(Order $order): array;
}

class V1OrderClient implements OrderClientInterface
{
    public function createOrder(Order $order): array
    {
        try {
            $response = Http::post('https://Dev.micros.services/api/v1/order', [
                'customer_name' => $order->name,
                'customer_nif' => $order->getTaxId()->taxId,
                'total' => $order->getMoney()->amount(),
                'currency' => $order->getMoney()->currency(),
                'items' => array_map(fn($item) => [
                    'sku' => $item->sku,
                    'qty' => $item->qty,
                    'unit_price' => $item->getMoney()->amount(),
                ], $order->getItems()),
            ]);

            return $response->json();

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Order V2 POST request failed: ' . $e->getMessage());
            // maybe throw exception
            throw new \Exception('Order V2 POST request failed: ' . $e->getMessage());
//            return response()->json(['error' => 'Failed to place order', 'details' => $e->getMessage()], 500);
        } catch (ConnectionException $e) {
//            return response()->json(['error' => 'Failed to place order', 'details' => $e->getMessage()], 500);
            throw new \Exception('Order V2 POST request connection failed: ' . $e->getMessage());
        }
        // if exception is abnormal will send the normal exception
    }
}

class V2OrderClient implements OrderClientInterface
{
    public function createOrder(Order $order): array
    {

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/vnd.api+json',
                'Accept' => 'application/vnd.api+json',
            ])->post('https://Dev.micros.services/api/v2/order', [
                'data' => [
                    'type' => 'orders', // Make sure this matches your API's expected resource type
                    'attributes' => [
                        'customer' => [
                            'name' => $order->name,
                            'nif' => $order->getTaxId()->taxId,
                        ],
                        'summary' => [
                            'currency' => $order->getMoney()->currency(),
                            'total' => $order->getMoney()->currency(),
                        ],
                        'lines' => array_map(fn($item) => [
                            'sku' => $item->sku,
                            'qty' => $item->qty,
                            'price' => $item->getMoney()->amount(),
                        ], $order->getItems()),
                    ],
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ]);

            return $response->json();

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Order V2 POST request failed: ' . $e->getMessage());
            // maybe throw exception
            throw new \Exception('Order V2 POST request failed: ' . $e->getMessage());
//            return response()->json(['error' => 'Failed to place order', 'details' => $e->getMessage()], 500);
        } catch (ConnectionException $e) {
//            return response()->json(['error' => 'Failed to place order', 'details' => $e->getMessage()], 500);
            throw new \Exception('Order V2 POST request connection failed: ' . $e->getMessage());
        }
        // if exception is abnormal will send the normal exception
    }
}
