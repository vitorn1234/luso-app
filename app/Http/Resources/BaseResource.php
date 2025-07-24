<?php

namespace App\Http\Resources;

use App\Domain\Order;
use Illuminate\Http\JsonResponse;

class BaseResource
{
    private Order $order;
    private string $version;
    public static array $allowedVersions = ['v1', 'v2'];

    // dont like copy paste but gonna elave this as is
    private function setVersion(string $version): self
    {
        if (!in_array($version, self::$allowedVersions, true)) {
            throw new \InvalidArgumentException("Invalid version: {$version}");
        }
        $this->version = $version;
        return $this;
    }
}
