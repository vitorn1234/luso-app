<?php

namespace App\Http\Controllers;

use App\Helper\AppHelper;
use App\Http\Resources\OrdersResource;
use App\Service\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function create(Request $request)
    {

        $apiVersion = $request->attributes->get('api_version');

        try {
            $validatedData = $request->validate(AppHelper::getValidatorVersion($apiVersion));

            $service = new Service($apiVersion,$validatedData);

            $service = $service->processOrder()->sendExternalService();  //we can define the service

            If($service->sent == false){
                Log::error($service->getError());
            }

            return (new OrdersResource($apiVersion,$service->getOrder()))->response();
        } catch (\Exception $e) {
            return AppHelper::getResponseError($apiVersion, $e);
        }
    }
}
