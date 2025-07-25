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
        // Validate amount as a valid decimal number, could force the 10.00
        // preg_match('/^\d+\.\d{2}$/', $string) === 1
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Total Amount must be a numeric value.');
        }

        // Optionally, validate currency code (ISO 4217)
        if (
            empty($currency) ||
            //!preg_match('/^[A-Z]{3}$/', $currency) ||
            $currency != 'EUR'
        ) { //switch to EUR validation
            throw new \InvalidArgumentException('Invalid currency code provided.');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Get the amount
     * @return string
     */
    public function amount(): float
    {
        return (float)$this->amount;
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
