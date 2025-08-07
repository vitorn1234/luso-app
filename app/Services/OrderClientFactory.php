<?php

namespace App\Services;

use App\Services\OrderClientInterface;
use App\Services\V1OrderClient;
use App\Services\V2OrderClient;

class OrderClientFactory
{
    public static function make(string $version)
    {
        return match ($version) {
            'v1' => new V1OrderClient(),
            'v2' => new V2OrderClient(),
            default => throw new \InvalidArgumentException("Unsupported version: $version"),
        };
    }
}
