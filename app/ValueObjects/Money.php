<?php

namespace App\ValueObjects;

class Money
{
    public function __construct(
        public readonly string $amount,
        public readonly string $currency
    )
    {

    }

    public static function fromString(string $amount, string $currency): self
    {
// Basic validation could be added here
        return new self($amount, $currency);
    }

    public function __toString(): string
    {
        return $this->amount;
    }
}
