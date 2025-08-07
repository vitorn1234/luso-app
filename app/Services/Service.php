<?php

namespace App\Services;

use App\Domain\Money;
use App\Domain\Order;
use App\Domain\TaxId;
use App\Domain\Item;
use App\Domain\User;
use App\Helper\AppHelper;
use App\Http\Resources\OrdersResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Service
{
    private array $validatedData;
    private string $version;
    public static array $allowedVersions = ['v1', 'v2'];
    private Order $order;
    public bool $sent = false;
    private array $processedData;

    public function __construct(string $version, array $validatedBodyData)
    {
        // improve create base class and extend it to both service and ordersResouce
        // with $version and order and
        $this->setVersion($version);
        $this->validatedData = $validatedBodyData;
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function createOrder(): self
    {
        $items = [];
        try {
            // Create the Order
            switch ($this->version) {
                case 'v1':
                    // could switch this into it's own method
                    $taxId = new TaxId($this->validatedData['customer_nif']);
                    $money = new Money($this->validatedData['total'], $this->validatedData['currency']);
                    foreach ($this->validatedData['items'] as $itemData) {
                        $items[] = new Item(
                            $itemData['sku'],
                            $itemData['qty'],
                            (new Money($itemData['unit_price'], $this->validatedData['currency']))
                        );
                    }
                    $order = new Order($this->validatedData['customer_name'], $taxId, $money, $items);
                    break;
                case 'v2':
                    // could switch this into its own method
                    $attr = $this->validatedData['data']['attributes'];
                    $taxId = new TaxId($attr['customer']['nif']);
                    $money = new Money($attr['summary']['total'], $attr['summary']['currency']);
                    foreach ($attr['lines'] as $itemData) {
                        $items[] = new Item(
                            $itemData['sku'],
                            $itemData['qty'],
                            (new Money($itemData['price'], $attr['summary']['currency']))
                        );
                    }
                    $order = new Order($attr['customer']['name'], $taxId, $money, $items);
                    break;
                default:
                    throw new \Exception('Version not valid');
            }
        } catch (\Exception $e) {
            //see if we need to process anything here or create our own exceptions
            throw $e;
        }

        $this->order = $order;

        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function sendExternalService($url = "Dev.micros.services"): self
    {
        try {
            $this->processData();

            $response = Http::post($url, $this->processedData);
            // Check if the request was successful (status code 2xx)
            $this->sent = $response->successful();
        } catch (\Exception $e) {
            //throw new \Exception("Error when trying to send to external Service". $e->getMessage());
            Log::error("Error when trying to send to external Service: " . $e->getMessage());
            // the external service is something
        }

        return $this;
    }

    private function setVersion(string $version): self
    {
        if (!in_array($version, self::$allowedVersions, true)) {
            throw new \InvalidArgumentException("Invalid version: {$version}");
        }
        $this->version = $version;
        return $this;
    }

    public function processData(): self
    {
        $this->processedData = (new OrdersResource($this->version, $this->getOrder()))->toArray();
        return $this;
    }

    public function getProcessData(): array
    {
        return $this->processedData;
    }
}
