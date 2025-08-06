<?php

namespace App\Service;

use App\DTO\OrderRequest;
use App\DTO\OrderRequestV1;
use App\DTO\OrderRequestV2;
use InvalidArgumentException;

class OrderRequestFactory
{
    public static function make(string $version, array $data): OrderRequest
    {
        return match ($version) {
            'v1' => OrderRequestV1::fromArray($data),
            'v2' => OrderRequestV2::fromArray($data),
            default => throw new InvalidArgumentException("Unsupported version: $version"),
        };
    }
}
