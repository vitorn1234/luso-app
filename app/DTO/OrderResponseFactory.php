<?php

namespace App\Service;

use App\DTO\OrderRequest;
use App\DTO\OrderRequestV1;
use App\DTO\OrderRequestV2;
use App\DTO\OrderResponse;
use App\DTO\OrderResponseV1;
use App\DTO\OrderResponseV2;
use InvalidArgumentException;

class OrderResponseFactory
{
    public static function make(string $version, array $data): OrderResponse
    {
        return match ($version) {
            'v1' => OrderResponseV1::fromArray($data),
            'v2' => OrderResponseV2::fromArray($data),
            default => throw new InvalidArgumentException("Unsupported version: $version"),
        };
    }
}
