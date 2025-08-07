<?php

namespace App\Http\Controllers;

use App\Helper\AppHelper;
use App\Http\Resources\OrdersResource;
use App\Services\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function create(Request $request): \Illuminate\Http\JsonResponse
    {

        $apiVersion = $request->attributes->get('api_version');

        try {
            $validatedData = $request->validate(AppHelper::getValidatorVersion($apiVersion));
            // Start Service
            $service = new Service($apiVersion, $validatedData);
            // create Order data, processOrder(uuid,number,createdAt) and then send to the external service
            $service = $service->createOrder()->sendExternalService();  //we can define the service
            if (!$service->sent) {
                Log::error("Failed to send to External Service");
            }

            // return response with correct headers
            return (new OrdersResource($apiVersion, $service->getOrder()))->response(201);
        } catch (\Exception $e) {
            return AppHelper::getResponseError($apiVersion, $e);
        }
    }
}
