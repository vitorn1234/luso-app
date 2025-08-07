<?php

// app/DTO/OrderRequestV1.php
namespace App\DTO;

class OrderRequestV1 extends OrderRequest
{
    public string $customer_name;
    public string $customer_nif;
    public string $total;
    public string $currency;
    public array $items; // array of item data

    /**
     * Factory method to create an instance from an array with validation.
     * @param array $data Incoming request payload
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->validateAndAssignAttributes($data);

        return $instance;
    }

    /**
     * Validates and assigns the 'data' array.
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validateAndAssignAttributes(array $data): void
    {
        // Validate customer_name
        if (!isset($data['customer_name']) || empty(trim($data['customer_name']))) {
            throw new \InvalidArgumentException("Invalid or missing `customer_name` in data");
        }

        // Validate customer_nif
        if (!isset($data['customer_nif']) || empty(trim($data['customer_nif']))) {
            throw new \InvalidArgumentException("Invalid or missing `customer_nif` in data");
        }

        // Validate customer details
        $this->validateCustomer($data['customer_name'], $data['customer_nif']);

        // Assign customer details
        $this->customer_name = $data['customer_name'];
        $this->customer_nif = $data['customer_nif'];

        // Validate total
        if (!isset($data['total']) || empty(trim($data['total']))) {
            throw new \InvalidArgumentException("Invalid or missing `total` in data");
        }

        // Validate currency
        if (!isset($data['currency']) || empty(trim($data['currency']))) {
            throw new \InvalidArgumentException("Invalid or missing `currency` in data");
        }

        // Validate summary
        $this->validateSummary($data['currency'], $data['total']);

        // Assign total and currency
        $this->total = $data['total'];
        $this->currency = $data['currency'];

        // Validate items
        if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
            throw new \InvalidArgumentException('Missing, invalid, or empty `items` in data');
        }

        // Validate lines/items
        $this->validateLines($data['items']);

        // Assign items
        $this->items = $data['items'];
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
