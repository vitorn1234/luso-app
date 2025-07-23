<?php

namespace App\Domain;

class User
{
    private string $name;
    private TaxId $taxId;

    /**
     * Constructor
     * @param string $name
     * @param TaxId $taxId
     */
    public function __construct(string $name, TaxId $taxId)
    {
        $this->name = $name;
        $this->taxId = $taxId;
    }

    /**
     * Get the user's name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the user's Tax ID as string
     * @return string
     */
    public function getTaxId(): string
    {
        return $this->taxId->getTaxId();
    }

    /**
     * Optional: String representation for debugging
     */
    public function __toString(): string
    {
        return sprintf('User: %s, Tax ID: %s', $this->name, $this->getTaxId());
    }
}
