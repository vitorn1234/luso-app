<?php

// app/DTO/OrderRequestV1.php
namespace App\DTO;

class OrderRequestV2 extends OrderRequest
{
    public string $type;
    public array $attributes;

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
        $instance->validateAndAssignData($data['data']);

        return $instance;
    }

    /**
     * Validates and assigns the 'data' array.
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validateAndAssignData(array $data): void
    {
        // Validate 'type'
        if (!isset($data['type']) || $data['type'] !== 'orders') {
            throw new \InvalidArgumentException('Invalid or missing "type". Must be "orders".');
        }
        $this->type = $data['type'];

        // Validate 'attributes'
        if (!isset($data['attributes']) || !is_array($data['attributes'])) {
            throw new \InvalidArgumentException('"attributes" is missing or not an array.');
        }
        $this->attributes = $data['attributes'];

        $this->validateAttributes($this->attributes);
    }

    /**
     * Validates 'attributes' structure.
     * @param array $attributes
     * @throws \InvalidArgumentException
     */
    protected function validateAttributes(array $attributes): void
    {
        // Customer validation
        if (!isset($attributes['customer']) || !is_array($attributes['customer'])) {
            throw new \InvalidArgumentException('Missing or invalid "customer" in attributes.');
        }

        if (!isset($attributes['customer']['name']) || !isset($attributes['customer']['nif'])) {
            throw new \InvalidArgumentException('Missing or invalid "customer parameters" in attributes.');
        }

        $this->validateCustomer($attributes['customer']['name'], $attributes['customer']['nif']);

        // Summary validation
        if (!isset($attributes['summary']) || !is_array($attributes['summary'])) {
            throw new \InvalidArgumentException('Missing or invalid "summary" in attributes.');
        }

        if (!isset($attributes['summary']['currency'])) {
            throw new \InvalidArgumentException('Missing or invalid "currency" in attributes.');
        }

        if (!isset($attributes['summary']['total'])) {
            throw new \InvalidArgumentException('Missing or invalid "total" in attributes.');
        }

        $this->validateSummary($attributes['summary']['currency'], $attributes['summary']['total']);

        // Lines validation
        if (!isset($attributes['lines']) || !is_array($attributes['lines']) || empty($attributes['lines'])) {
            throw new \InvalidArgumentException('Missing, invalid, or empty "lines" in attributes.');
        }
        $this->validateLines($attributes['lines']);
    }
}
