<?php

namespace App\Domain;

use App\Domain\Item;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Self_;

class Order
{
    private User $user;
    private Money $money;
    /** @var Item[] */
    private array $items;
    private string $uuid;
    private string $number;
    private string $status;
    // depends on how we gonna use the date if there any format necessary or not
    private string /*?\DateTimeInterface*/ $createdAt;

    /**
     * Item constructor.
     * @param User $user
     * @param Money $qty
     * @param Item[] $items
     */
    public function __construct(User $user, Money $money, array $items)
    {
        $this->user = $user;
        $this->money = $money;

        $this->validateItems($items);
        $this->items = $items;
        $this->processOrder();
    }

    protected function processOrder(): void
    {
        $this->uuid = (string) Str::uuid();

        // Process order number
        $this->number  = 'ORD-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0');
        $this->createdAt = (new \DateTimeImmutable())->format(\DateTime::ATOM);
    }

    protected function validateItems($items): void
    {
        if (count($items) <= 0) {
            throw new \InvalidArgumentException('At least one item must be provided.');
        }
        $total = 0;
        foreach ($items as $item) {
            if (!($item instanceof Item)) {
                throw new \InvalidArgumentException('All items must be instances of Item');
            }
            $total += (int)$item->getQty() * (int)$item->getPrice();
        }

        $totalMain = $this->money->amount();
        if ((int)$totalMain != (int)$total) {
            throw new \InvalidArgumentException(
                "Total $totalMain defined does not match the sum of the ordered items $total"
            );
        }
    }
    // Getter for user
    public function getUser(): User
    {
        return $this->user;
    }

    // Getter for quantity
    public function getMoney(): Money
    {
        return $this->money;
    }

    // Getter for sub-items
    public function getItems(): array
    {
        return $this->items;
    }

    // Getter for order UUID
    public function getUuid(): string
    {
        return $this->uuid;
    }

    // Getter for order number
    public function getNumber(): string
    {
        return $this->number;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getArray(): array
    {
        return get_object_vars($this);
    }
    /**
     * Create a new Order with items and save it to DB
     */
    public function saveDB(): self
    {
        try {
            // Create order
            DB::beginTransaction();
            // Create order
            $order = new \App\Models\Order();
            $order->customer_name = $this->user->getName();
            $order->customer_nif = $this->user->getTaxId();
            $order->total = $this->getMoney()->amount();
            $order->currency = $this->getMoney()->currency();
            $order->number = $this->number;
            $order->uuid = $this->uuid;
            $order->created_at = now();
            $order->save();
            // Save items
            foreach ($this->items as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->sku = $item->getSku();
                $orderItem->qty = $item->getQty();
                $orderItem->unit_price = $item->getPrice();
                $orderItem->save();
//            $order->items()->create([
//                'item_id' => $item->getSku(),
//                'quantity' => $item->getQty(),
//                'item_price' => $item->getPrice(),
//            ]);
            }

            DB::commit();
            return $this; // Return $this for method chaining
        } catch (\Exception $e) {
            DB::rollBack();
            // Important: Log the error for debugging
            \Log::error("Error saving order: " . $e->getMessage());
            throw $e; // Re-throw the exception to handle it elsewhere
        }
    }
}
