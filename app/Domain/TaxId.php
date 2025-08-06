<?php

namespace App\Domain;

class TaxId
{
    public string $taxId;

    /**
     * Constructor validates and sets the tax ID.
     * @param string $taxId
     * @throws \InvalidArgumentException if the tax ID is invalid
     */
    public function __construct(string $taxId)
    {
        if (!$this->validateNIF($taxId)) {
            throw new \InvalidArgumentException('Invalid Tax ID (NIF).');
        }
        $this->taxId = $taxId;
    }

    /**
     * Validate the NIF (tax ID)
     * @param string $nif
     * @return bool
     */
    private function validateNIF(string $nif): bool
    {
        // Check format: 9 digits
        if (!preg_match('/^[0-9]{9}$/', $nif)) {
            return false;
        }

        // Convert string to array of digits
        $digits = str_split($nif);

        // Extract the check digit (last digit)
        $checkDigit = (int) $digits[8];

        // Calculate the weighted sum of the first 8 digits
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int) $digits[$i] * (9 - $i);
        }

        // Compute the expected check digit
        $rest = $sum % 11;
        $expectedCheckDigit = (11 - $rest) % 10;

        // Check if the calculated check digit matches the actual one
        return $checkDigit === $expectedCheckDigit;
    }

    /**
     * String representation
     * @return string
     */
    public function __toString(): string
    {
        return $this->taxId;
    }
}
