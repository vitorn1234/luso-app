<?php

namespace App\Service;

use App\Domain\Money;
use App\Domain\Order;
use App\Domain\TaxId;
use App\Domain\Item;
use App\Domain\User;
use App\Helper\AppHelper;

class Service
{
    private array $validatedData;
    private string $version;
    private string $order;

    public function __construct(string $version, array $validatedBodyData)
    {
        $this->version = $version;
        $this->validatedData = $validatedBodyData;
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function processOrder() : self
    {
        $items =[];
        try {
            // Create the Order
            switch ($this->version) {
                case 'v1':
                    // Doubt about taxId
                    $taxId = new TaxId($this->validatedData['customer_nif']);
                    $user= new User($this->validatedData['customer_name'],$taxId);
                    $money = new Money($this->validatedData['total'],$this->validatedData['currency']);
                    foreach ($this->validatedData['items'] as $itemData) {
                        $items[] = new Item($itemData['sku'],$itemData['qty'],$itemData['unit_price']);
                    }
                    $order = new Order($user, $money, $items);
                    break;
                case 'v2':

                    $attr= $this->validatedData['data']['attributes'];
                    $taxId = new TaxId($attr['customer']['nif']);
                    $user= new User($attr['customer']['name'],$taxId);
                    $money = new Money($attr['summary']['total'],$attr['summary']['currency']);
                    foreach ($attr['lines'] as $itemData) {
                        $items[] = new Item($itemData['sku'],$itemData['qty'],$itemData['price']);
                    }
                    $order = new Order($user, $money, $items);
                    break;
                default:

            }
        } catch (\Exception $e) {
            //see if we need to process anything here or create our own exceptions
            throw $e;
        }

        $this->order= $order;

        return $this;
        //return $order;
    }
}
