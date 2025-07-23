<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use App\Rules\ValidNIF;

class OrderController extends Controller
{
    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request
        try {
            $validatedData = $request->validate([
                'customer_name' => 'required|string',
                'customer_nif' => ['required','string', new ValidNIF],
                'total' => 'required|numeric',
                'currency' => 'required|string|in:EUR', //try with USD
                'items' => 'required|array|min:1',
                'items.*.sku' => 'required|string',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => "Failed processing body", 'message' => $e->getMessage()], 400);
        }

        // Generate UUID for the order
        $uuid = (string) Str::uuid();

        // create "ORD-2025-00001"
        // TODO figure out how to get the last part of the ORD
//        $lastOrder = Order::orderBy('id', 'desc')->first();
//        $lastNumber = $lastOrder ? intval(substr($lastOrder->number, 9)) : 0;
//        $lastNumber = $lastOrder ? intval($lastOrder->id) : 0;
//        $newNumber = $lastNumber + 1;
        $lastNumber = random_int(0,99998);
        $newNumber = $lastNumber + 1;
        $orderNumber = 'ORD-' . date('Y') . '-' . str_pad($newNumber, 5, '0');

        DB::beginTransaction();

        try {
            // Create the Order
            $order = Order::create([
                'customer_name' => $validatedData['customer_name'],
                'customer_nif' => $validatedData['customer_nif'],
                'total' => $validatedData['total'],
                'currency' => $validatedData['currency'],
                'number' => $orderNumber,
                'uuid' => $uuid,
                // Add other fields if needed
            ]);
            $total = 0;
            // Create Order Items
            foreach ($validatedData['items'] as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'sku' => $itemData['sku'],
                    'qty' => $itemData['qty'],
                    'unit_price' => $itemData['unit_price'],
                ]);
                $total += intval(['qty']) * intval($itemData['unit_price']);
            }

            if ($total !=  intval($validatedData['total'])){
                return response()->json(['error' => "Failed processing body",
                    'message' => "Total $validatedData[total] defined does not match the sum of the ordered items $total"], 400);
            }
            // DB::commit();

        } catch (\Exception $e) {
            // DB::rollBack();
            return response()->json(['error' => 'Failed to create order', 'message' => $e->getMessage()], 500);
        }

        // return response TODO create Resource
        $responseData = [
            'data' => [
                'uuid' => $order->uuid,
                'number' => $order->number,
                'status' => 'created',
                'total' => $order->total,
                'currency' => $order->currency,
                'created_at' => $order->created_at->toIso8601String(),
            ],
        ];

        return response()->json($responseData, 201); // 201 - Resource created successfully

    }
}
