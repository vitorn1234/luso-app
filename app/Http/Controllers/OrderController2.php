<?php

namespace App\Http\Controllers;

use App\DTO\OrderBuildFactory;
use App\DTO\OrderRequestFactory;
use App\Helper\AppHelper;
use App\Services\OrderClientFactory;
use App\DTO\OrderResponseFactory;
use Illuminate\Http\Request;

class OrderController2 extends Controller
{
    public function orderIntegrationTest(Request $request, string $version): \Illuminate\Http\JsonResponse
    {
        $data = $request->json()->all();
        return $this->orderIntegration($data, $version);
    }

    public function orderIntegration(array $data, string $version): \Illuminate\Http\JsonResponse
    {

        try {
            // create client for selected version
            $client = OrderClientFactory::make($version);
            $versionCapitalized = strtoupper($version);
            //process request data
            $dtoRequest = OrderRequestFactory::make($version, $data);

            // Create base object
            $order = OrderBuildFactory::make($version, $dtoRequest);
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

//    public function returnError()
//    {
//        $message = "Line/Item at index 1: 'sku' must be a string.";
//        $version = 1; // or 2
//
//        if ($version == 1) {
//            // Remove "Line"
//            $cleaned_message = preg_replace('/Line/', '', $message);
//        } elseif ($version == 2) {
//            // Remove "Item"
//            $cleaned_message = preg_replace('/Item/', '', $message);
//        } else {
//            // No change for other versions
//            $cleaned_message = $message;
//        }
//
//        echo $cleaned_message;
//    }
}
