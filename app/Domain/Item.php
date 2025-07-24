<?php

namespace App\Domain;

class Item
{
    private string $sku;
    private int $qty;        // Quantity as integer
    private string $price;    // Price as string/float

    /**
     * Item constructor.
     * @param string $sku
     * @param int|string $qty
     * @param float|string $price
     * @throws \InvalidArgumentException
     */
    public function __construct(string $sku, int $qty, string $price)
    {
        $this->sku = $sku;

        // Validate quantity: must be non-negative
        if ($qty < 0) {
            throw new \InvalidArgumentException('Quantity must be a positive value.');
        }
        $this->qty = $qty;

        // Validate price: must be numeric
        if (!is_numeric($price)) {
            throw new \InvalidArgumentException('Price must be in a numeric format.');
        }
        $this->price = $price;
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
