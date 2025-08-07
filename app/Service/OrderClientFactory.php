<?php

namespace App\Service;

use App\Service\OrderClientInterface;
use App\Service\V1OrderClient;
use App\Service\V2OrderClient;

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
