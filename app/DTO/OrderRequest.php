<?php

// app/DTO/OrderRequest.php
namespace App\DTO;

class OrderRequest
{
    // put all generic validations
    protected function validateCustomer(string $name, string $nif): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("Customer field name is missing or invalid.");
        }

        // Validate NIF: portuguese
        if (!$this->validateNIF($nif)) {
            throw new \InvalidArgumentException('Invalid Tax ID (NIF).');
        }
    }

    protected function validateSummary(string $currency, string $total): void
    {
        if (empty(trim($currency))) {
            throw new \InvalidArgumentException("Field 'currency' is missing or invalid.");
        }

        if (empty(trim($total))) {
            throw new \InvalidArgumentException("Field 'total' is missing or invalid.");
        }

        // Validate total as a valid decimal string
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $total)) {
            throw new \InvalidArgumentException(
                'Total must be a valid decimal number with up to 2 decimal places.'
            );
        }

        // Validate currency code (basic check, could be expanded)
        if (strlen($currency) !== 3) {
            throw new \InvalidArgumentException('Currency code must be 3 characters.');
        }
    }

    protected function validateLines(array $lines): void
    {
        if (empty($lines)) {
            throw new \InvalidArgumentException('Lines/Items array cannot be empty.');  // to add removal of
            // lines or items + / called clean up or add versioning
        }

        foreach ($lines as $index => $line) {
            if (!is_array($line)) {
                throw new \InvalidArgumentException("Line/Item at index {$index} must be an array.");
            }
            $this->validateLine(
                $line['sku'] ?? '',
                $line['qty'] ?? '',
                ($line['price'] ?? $line['unit_price']) ?? '',
                $index
            );
        }
    }

    private function validateLine($sku, $qty, $price, int $index): void
    {
        $requiredFields = ['sku' => $sku, 'qty' => $qty, 'price/unit_price' => $price];

        foreach ($requiredFields as $field => $value) {
            if (empty(trim($value))) {
                throw new \InvalidArgumentException("Line/Item at index {$index} missing '{$field}'.");
            }
        }

        if (!is_string($sku)) {
            throw new \InvalidArgumentException("Line/Item at index {$index}: 'sku' must be a string.");
        }

        if (!is_int($qty) || $qty <= 0) {
            throw new \InvalidArgumentException("Line/Item at index {$index}: 'qty' must be a positive integer.");
        }

        if (!is_string($price) || !preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
            throw new \InvalidArgumentException(
                "Line/Item at index {$index}: 'price' must be a valid decimal number with up to 2 decimal places."
            );
        }
    }

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
}
