<?php

namespace App\Domain;

class Money
{
    private string $amount;   // stored as string for exact decimal representation
    private string $currency;

    /**
     * Money constructor.
     * @param string $amount
     * @param string $currency
     * @throws \InvalidArgumentException
     */
    public function __construct(string $amount, string $currency)
    {
        // Validate amount as a valid decimal number
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Amount must be a numeric value.');
        }

        // Optionally, validate currency code (ISO 4217)
        if (empty($currency) || !preg_match('/^[A-Z]{3}$/', $currency)) { //switch to EUR validation
            throw new \InvalidArgumentException('Invalid currency code.');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Get the amount
     * @return string
     */
    public function amount(): string
    {
        return $this->amount;
    }

    /**
     * Get the currency
     * @return string
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * String representation of Money
     * @return string
     */
    public function __toString(): string
    {
        return $this->currency . ' ' . $this->amount;
    }
}
