<?php

namespace App\Helper;

use App\Exceptions\LogicValidationException;
use App\Rules\ValidNIF;
use Illuminate\Http\JsonResponse;

class AppHelper
{
    public static function getValidatorVersion($version)
    {
        self::validateVersion($version);

        return match ($version) {
            'v1' => [
                'customer_name' => 'required|string',
                'customer_nif' => ['required', 'string', new ValidNIF()],
                'total' => 'required|numeric',
                'currency' => 'required|string|in:EUR', //try with USD
                'items' => 'required|array|min:1',
                'items.*.sku' => 'required|string',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric',
            ],
            'v2' => [
                'data.type' => 'required|string|in:orders',
                'data.attributes.customer.name' => 'required|string',
                'data.attributes.customer.nif' => ['required','string', new ValidNIF()],
                'data.attributes.summary.currency' => 'required|string|in:EUR', //try with USD
                'data.attributes.summary.total' => 'required|string|min:1',
                'data.attributes.lines' => 'required|array',
                'data.attributes.lines.*.sku' => 'required|string',
                'data.attributes.lines.*.qty' => 'required|integer|min:1',
                'data.attributes.lines.*.price' => 'required|numeric',
            ],
            default => throw new \Exception('Invalid version format')
        };
    }

    public static function getResponseError($version, $e, $status = 400): JsonResponse
    {
        self::validateVersion($version);
        return match ($version) {
            'v1' => self::respondErrorV1($e, $status),
            'v2' =>  self::respondErrorV2($e, $status),
            default => throw new \InvalidArgumentException("Invalid version: {$version}")
        };
    }

    private static function respondErrorV2($e, $status = 400): JsonResponse
    {
        if ($e instanceof \Illuminate\Validation\ValidationException
            || $e instanceof \App\Exceptions\LogicValidationException) {
            // Handle validation exception
            $errors = [];
            $status = 422;
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $pointer = '/' . str_replace('.', '/', $field);
                    $errors[] = [
                        'status' => $status,
                        'source' => ['pointer' => $pointer],
                        'title' => 'Invalid Attribute',
                        'detail' => $message
                    ];
                }
            }

            return response()->json(['errors' => $errors], $status, ['Content-Type' => 'application/vnd.api+json']);
        } else {
            // TODO work with all sort of errors return and return match get_class($e) or define our own
            $errorResponse = [
                'errors' => [
                    [
                        'status' => $status,
                        'title' => 'Internal Server Error',
                        'detail' => $e->getMessage(),
                        'code' => 'internal_server_error' // TODO fix issues with code definition and values
                    ]
                ]
            ];

            return response()->json($errorResponse, $status, ['Content-Type' => 'application/json']);
        }
    }

    private static function respondErrorV1($e, int $status): JsonResponse
    {
        $message = $e->getMessage();
        $error = "error";
        if ($e instanceof \Illuminate\Validation\ValidationException
            || $e instanceof \App\Exceptions\LogicValidationException) {
            $status = 422;
            // Handle validation exception
            $error = "Failed processing body";
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ], $status);

    }

    public static function validateVersion($version): void
    {
        $allowedVersions = ['v1', 'v2'];
        if (!in_array($version, $allowedVersions, true)) {
            throw new \InvalidArgumentException("Invalid version: {$version}");
        }
    }
}
