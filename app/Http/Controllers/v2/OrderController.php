<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use App\Rules\ValidNIF;

class OrderController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request
        try {
            // Validate JSON:API request structure
            $validatedData = $request->validate([
                'data.type' => 'required|string|in:orders',
                'data.attributes.customer.name' => 'required|string',
                'data.attributes.customer.nif' => ['required','string', new ValidNIF],
                'data.attributes.summary.currency' => 'required|string|in:EUR', //try with USD
                'data.attributes.summary.total' => 'required|string',
                'data.attributes.lines' => 'required|array',
                'data.attributes.lines.*.sku' => 'required|string',
                'data.attributes.lines.*.qty' => 'required|integer',
                'data.attributes.lines.*.price' => 'required|string',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
                $errors = [];
                foreach ($e->errors()->messages() as $field => $messages) {
                    foreach ($messages as $message) {
                        $pointer = $this->convertToPointer($field);
                        $errors[] = [
                            'status' => '422',
                            'source' => [ 'pointer' => $pointer ],
                            'title' => 'Invalid Attribute',
                            'detail' => $message
                        ];
                    }
                }
                return response()->json(['errors' => $errors], 422, ['Content-Type' => 'application/vnd.api+json']);
        }

        // Generate order ID
        $orderId = 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        // Generate UUID
        $uuid = Str::uuid()->toString();

        // Current timestamp in ISO 8601
        $createdAt = now()->toIso8601String();

        // Build JSON:API response
        $response = [
            'links' => [
                'self' => "https://micros.services/api/v2/order/{$orderId}"
            ],
            'data' => [
                'type' => 'orders',
                'id' => $orderId,
                'attributes' => [
                    'uuid' => $uuid,
                    'status' => 'created',
                    'currency' => $request->input('data.attributes.summary.currency'),
                    'total' => $request->input('data.attributes.summary.total'),
                    'created_at' => $createdAt,
                ],
            ],
        ];

        // Return response with 201 Created
        return response()->json($response, 201, ['Content-Type' => 'application/vnd.api+json']);
    }


}
