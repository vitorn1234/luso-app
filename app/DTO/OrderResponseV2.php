<?php

namespace App\DTO;

class OrderResponseV2 extends OrderResponse
{
    public string $selfLink;
    public string $number;
    public string $uuid;
    public string $status;
    public string $currency;
    public string $total;
    public string $created_at;

    /**
     * Factory method to create an instance from an array with validation.
     * @param array $data Incoming request payload
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        // Validate top-level structure and secondary since there is only 1
        if (
            !isset($response['links']['self']) ||
            !filter_var($response['links']['self'], FILTER_VALIDATE_URL)
        ) {
            throw new \InvalidArgumentException('Invalid or missing "links.self" URL');
        }
        // Validate top-level structure
        if (!isset($data['data'])) {
            throw new \InvalidArgumentException('Missing "data" key in payload.');
        }

        $instance = new self();
        $instance->validateAndAssignAttributes($data['data']);

        // Assign link
        $instance->selfLink = trim($data['links']['self']);

        return $instance;
    }

    private function validateAndAssignAttributes(array $data): void
    {
        // Validate 'id'
        if (
            !isset($data['id']) ||
            !preg_match('/^ORD-\d{4}-\d{5}$/', $data['id'])
        ) {
            throw new \InvalidArgumentException('Invalid or missing "id"');
        }
        $this->number = $data['id'];

        // Validate 'attributes' presence
        if (!isset($data['attributes'])) {
            throw new \InvalidArgumentException('Missing "attributes" in data');
        }
        $attributes = $data['attributes'];

        // Validate 'uuid'
        if (
            !isset($attributes['uuid']) ||
            !preg_match(
                '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                $attributes['uuid']
            )
        ) {
            throw new \InvalidArgumentException('Invalid or missing "uuid"');
        }
        $this->uuid = $attributes['uuid'];

        // Validate 'status'
        if (!isset($attributes['status']) || $attributes['status'] !== 'created') {
            throw new \InvalidArgumentException('Invalid or missing "status"');
        }
        $this->status = $attributes['status'];

        // Validate 'currency'
        if (!isset($attributes['currency']) || $attributes['currency'] !== 'EUR') {
            throw new \InvalidArgumentException('Invalid or missing "currency"');
        }
        $this->currency = $attributes['currency'];

        // Validate 'total'
        if (!isset($attributes['total']) || !is_numeric($attributes['total'])) {
            throw new \InvalidArgumentException('Invalid or missing "total"');
        }
        $this->total = $attributes['total'];

        // Validate 'created_at'
        if (
            !isset($attributes['created_at']) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $attributes['created_at'])
        ) {
            throw new \InvalidArgumentException('Invalid "created_at" format');
        }
        $this->created_at = $attributes['created_at'];
    }
}
