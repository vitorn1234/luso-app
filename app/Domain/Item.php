<?php

namespace App\Domain;

class Item
{
    public string $sku;
    public int $qty;        // Quantity as integer
    private Money $money;    // Price as string/float

    /**
     * Item constructor.
     * @param string $sku
     * @param int|string $qty
     * @param float|string $price
     * @throws \InvalidArgumentException
     */
    public function __construct(string $sku, int $qty, Money $price)
    {
        $this->sku = $sku;

        // Validate quantity: must be non-negative
        if ($qty < 0) {
            throw new \InvalidArgumentException('Quantity must be a positive value.');
        }
        $this->qty = $qty;
        $this->money = $price;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function __toString(): string
    {
        return sprintf('SKU: %s, Qty: %d, Price: %.2f', $this->sku, $this->qty, $this->money);
    }
}
