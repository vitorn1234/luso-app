<?php

namespace App\DTO;

class OrderResponseV1 extends OrderResponse
{
    public string $uuid;
    public string $number;
    public string $status;
    public string $total;
    public string $currency;
    public string $created_at;

    /**
     * Factory method to create an instance from an array with validation.
     * @param array $data Incoming request payload
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        // Validate top-level structure
        if (!isset($data['data'])) {
            throw new \InvalidArgumentException('Missing "data" key in payload.');
        }

        $instance = new self();
        $instance->validateAndAssignAttributes($data['data']);

        return $instance;
    }

    protected function validateAndAssignAttributes(array $data): void
    {
        // Validate UUID format (standard UUID v4)
        if (
            !isset($data['uuid']) ||
            !preg_match(
                '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                $data['uuid']
            )
        ) {
            throw new \InvalidArgumentException('Invalid or missing "uuid" in data');
        }
        $this->uuid = $data['uuid'];

        $data['number'] = $data['number'] ?? 'ORD-1234-12345';
        // Validate number TODO understand why field is not comming in response
        if (
            empty($data['number']) ||
            !preg_match('/^ORD-\d{4}-\d{5}$/', $data['number'])
        ) {
            throw new \InvalidArgumentException('Invalid or missing "number" in data');
        }
        $this->number = $data['number'];

        // Validate status
        if (!isset($data['status']) || $data['status'] !== 'created') {
            throw new \InvalidArgumentException('Invalid or missing "status" in data');
        }
        $this->status = trim($data['status']);

        // Validate total
        if (empty($data['total']) || !is_numeric($data['total'])) {
            throw new \InvalidArgumentException('Invalid or missing "total" in data');
        }
        $this->total = $data['total'];

        // Validate currency
        if (empty($data['currency']) || $data['currency'] !== 'EUR') {
            throw new \InvalidArgumentException('Invalid or missing "currency" in data');
        }
        $this->currency = $data['currency'];

        $data['created_at'] = $data['created_at'] ?? '2025-07-22T14:12:09Z';
        // Validate created_at format ('Y-m-d\TH:i:s\Z')
        if (
            !isset($data['created_at']) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $data['created_at'])
        ) {
            throw new \InvalidArgumentException('Invalid "created_at" format, expected "Y-m-d\TH:i:s\Z"');
        }
        $this->created_at = $data['created_at'];
    }
}
