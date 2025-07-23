<?php

namespace App\Domain;
class TaxId
{
    private string $taxId;

    /**
     * Constructor validates and sets the tax ID.
     * @param string $taxId
     * @throws \InvalidArgumentException if the tax ID is invalid
     */
    public function __construct(string $taxId)
    {
        $normalizedTaxId = strtoupper(trim($taxId));
        if (!$this->validateNIF($normalizedTaxId)) {
            throw new \InvalidArgumentException('Invalid Tax ID (NIF).');
        }
        $this->taxId = $normalizedTaxId;
    }

    /**
     * Get the tax ID
     * @return string
     */
    public function getTaxId(): string
    {
        return $this->taxId;
    }

    /**
     * Validate the NIF (tax ID)
     * @param string $value
     * @return bool
     */
    private function validateNIF(string $value): bool
    {
        // Check format: 8 digits + 1 letter
        if (!preg_match('/^[0-9]{8}[A-Z]$/', $value)) {
            return false;
        }

        $numbers = substr($value, 0, 8);
        $letter = substr($value, -1);
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        // Calculate checksum
        $index = intval($numbers) % 23;
        return $letter === $letters[$index];
    }

    /**
     * String representation
     * @return string
     */
    public function __toString(): string
    {
        return $this->taxId;
    }

    /**
     * Check if the current tax ID is valid (redundant if constructor validates)
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->validateNIF($this->taxId);
    }
}
