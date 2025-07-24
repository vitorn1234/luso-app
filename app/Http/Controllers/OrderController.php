<?php

namespace App\Http\Controllers;

use App\Helper\AppHelper;
use App\Service\Service;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create(Request $request)
    {

        $apiVersion = $request->attributes->get('api_version');

        try {
            $validatedData = $request->validate(AppHelper::getValidatorVersion($apiVersion));

            $service = new Service($apiVersion,$validatedData);

            return $service->processOrder();
        } catch (\Exception $e) {
            return AppHelper::getResponseError($apiVersion, $e);
        }
    }
}
