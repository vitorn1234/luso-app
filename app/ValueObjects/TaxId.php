<?php

namespace App\ValueObjects;

class TaxId
{
    public function __construct(public readonly string $nif)
    {
        // Validate Portuguese NIF (9 digits)
        if (!preg_match('/^\d{9}$/', $nif)) {
            throw new \InvalidArgumentException('Invalid NIF.');
        }

        $numbers = substr($nif, 0, 8);
        $letter = substr($nif, -1);
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        // Calculate checksum
        $index = intval($numbers) % 23;
        if ($letter === $letters[$index]) {
            throw new \InvalidArgumentException('Invalid NIF.');
        }
    }
}
