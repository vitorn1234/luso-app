<?php

namespace App\Domain;

class Item
{
    private string $sku;
    private int $qty;        // Quantity as integer
    private float $price;    // Price as float

    /**
     * Item constructor.
     * @param string $sku
     * @param int|string $qty
     * @param float|string $price
     * @throws \InvalidArgumentException
     */
    public function __construct(string $sku, $qty, $price)
    {
        $this->sku = $sku;

        // Validate and cast quantity
        if (is_string($qty) && !ctype_digit($qty)) {
            throw new \InvalidArgumentException('Quantity must be a non-negative integer.');
        }
        $this->qty = (int) $qty;

        // Validate and cast price
        if (is_string($price) && !is_numeric($price)) {
            throw new \InvalidArgumentException('Price must be a numeric value.');
        }
        $this->price = (float) $price;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function __toString(): string
    {
        return sprintf('SKU: %s, Qty: %d, Price: %.2f', $this->sku, $this->qty, $this->price);
    }
}
