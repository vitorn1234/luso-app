<?php

namespace App\Http\Resources;

use App\Domain\Order;
use Illuminate\Http\JsonResponse;

class OrdersResource
{
    private Order $order;
    private string $version;
    public static array $allowedVersions = ['v1', 'v2'];

    public function __construct($version, Order $order)
    {
        $this->setVersion($version);
        $this->order = $order;
    }

    /**
     * Converts the resource to an array based on version
     *
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return match ($this->version) {
            'v1' => [
                'data' => [
                    'uuid' => $this->order->getUuid(),
                    'number' => $this->order->getNumber(),
                    'status' => 'created', // Consider using $this->order->getStatus() if available
                    'total' => $this->order->getMoney()->amount(),
                    'currency' => $this->order->getMoney()->currency(),
                    'created_at' => now()->toIso8601String(),  // Replace when the datetime should be created
                    //$this->order->getCreatedAt()?->toIso8601String()
                ],
            ],
            'v2' => [
                'links' => [
                    'self' => "https://micros.services/api/v2/order/{$this->order->getNumber()}",
                ],
                'data' => [
                    'type' => 'orders',
                    'id' => $this->order->getNumber(),
                    'attributes' => [
                        'uuid' => $this->order->getUuid(),
                        'status' => 'created', // Replace with actual status if available
                        'total' => $this->order->getMoney()->amount(),
                        'currency' => $this->order->getMoney()->currency(),
                        'created_at' => now()->toIso8601String(), // Replace when the datetime should be created
                        //(new \DateTimeImmutable())->format(\DateTime::ATOM)
                    ],
                ],
            ],
            default => throw new \Exception('Version not valid'),
        };
    }

    /**
     * Converts the resource to a JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Returns a JSON response with appropriate headers
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function response(): JsonResponse
    {
        switch ($this->version) {
            case 'v1':
                return response()->json($this->toArray(), 200, ['Content-Type' => 'application/json']);
            case 'v2':
                return response()->json($this->toArray(), 200, ['Content-Type' => 'application/vnd.api+json']);
            default:
                throw new \Exception('Version not valid');
        }
    }

    // dont like copy paste but gonna elave this as is
    private function setVersion(string $version): void
    {
        if (!in_array($version, self::$allowedVersions, true)) {
            throw new \InvalidArgumentException("Invalid version: {$version}");
        }
        $this->version = $version;
    }
}
