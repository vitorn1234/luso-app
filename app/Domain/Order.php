<?php

namespace App\Domain;

class Order
{
    private User $user;
    private Money $qty;
    /** @var Item[] */
    private array $items;

    /**
     * Item constructor.
     * @param User $user
     * @param Money $qty
     * @param Item[] $items
     */
    public function __construct(User $user, Money $money, array $items )
    {
        foreach ($items as $line) {
            if (!$line instanceof Item) {
                throw new \InvalidArgumentException('All items must be instances of Item');
            }
        }

        $this->user = $user;
        $this->money = $money;
        $this->items = $items;
    }


    // Getter for user
    public function getUser(): User
    {
        return $this->user;
    }

    // Getter for quantity
    public function getQty(): Money
    {
        return $this->qty;
    }

    // Getter for sub-items
    public function getItems(): array
    {
        return $this->items;
    }
}
