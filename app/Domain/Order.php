<?php

namespace App\Domain;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicValidationException;

class Order
{
    public string $name;
    private TaxId $taxId;
    private Money $money;
    /** @var Item[] */
    private array $items;
    public string $uuid = "e3d4b1d2-97db-4e5d-a7f5-7c9f7b1c2e10";
    public string $number = 'ORD-2025-00001';
    public string $status = "created";
    // depends on how we're going to use the date if there is any format necessary or not
    public string /*?\DateTimeInterface*/ $createdAt = "2025-07-22T14:12:09Z";

    /**
     * Item constructor.
     * @param string $name
     * @param TaxId $taxId
     * @param Money $qty
     * @param Item[] $items
     */
    public function __construct(string $name, TaxId $taxId, Money $money, array $items)
    {
        $this->name = $name;
        $this->money = $money;
        $this->taxId = $taxId;
        $this->validateItems($items);
        $this->items = $items;
//        $this->processOrder();
    }

//    protected function processOrder(): void
//    {
//        $this->uuid = (string) Str::uuid();
//
//        // Process order number
//        $this->number  = 'ORD-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0');
//        $this->createdAt = now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
//    }

    protected function validateItems($items): void
    {
//                0 => "The data.type field is required."
        if (count($items) <= 0) {
            $message = 'At least one item must be provided.';
            throw new LogicValidationException($message, ["data.attributes.lines" => [$message]]);
        }
        $total = 0;
        foreach ($items as $item) {
            if (!($item instanceof Item)) {
                $message = 'All items must be instances of Item';
                throw new LogicValidationException($message, ['data.attributes.lines' => [$message]]);
            }
            $total += (int)$item->qty * (int)$item->getMoney()->amount();
        }

        $totalMain = $this->money->amount();
        if ((int)$totalMain != (int)$total) {
            $message = "Total $totalMain defined does not match the sum of the ordered items $total";
            throw new LogicValidationException($message, ['data.attributes.summary.total' => [$message]]);
        }
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
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getTaxId(): TaxId
    {
        return $this->taxId;
    }

    // Setter for order number
    public function setStatus(string $number): void
    {
        $this->number = $number;
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
            $order->customer_name = $this->name;
            $order->customer_nif = $this->getTaxId()->taxId;
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
                $orderItem->sku = $item->sku;
                $orderItem->qty = $item->qty;
                $orderItem->unit_price = $item->getMoney()->amount();
                $orderItem->save();
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

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
