<?php

namespace App\DTO;

use App\Domain\Item;
use App\Domain\Money;
use App\Domain\Order;
use App\Domain\TaxId;

class OrderBuildFactory
{
    public static function make(string $version, $data): Order
    {
        return match ($version) {
            'v1' => self::buildOrderFromV1($data),
            'v2' => self::buildOrderFromV2($data),
            default => throw new \InvalidArgumentException("Unsupported version: $version"),
        };
    }

    protected static function buildOrderFromV1(OrderRequestV1 $dto): Order
    {
        $money = new Money($dto->total, $dto->currency);
        $taxId = new TaxId($dto->customer_nif);
        $items = array_map(fn($item) => new Item(
            $item['sku'],
            $item['qty'],
            new Money($item['unit_price'], $dto->currency)
        ), $dto->items);

        return new Order($dto->customer_name, $taxId, $money, $items);
    }

    protected static function buildOrderFromV2(OrderRequestV2 $dto): Order
    {
        $attributes = $dto->attributes;
        $customer = $attributes['customer'];
        $summary = $attributes['summary'];
        $lines = $attributes['lines'];

        $money = new Money($summary['total'], $summary['currency']);
        $taxId = new TaxId($customer['nif']);
        $items = array_map(fn($line) => new Item(
            $line['sku'],
            $line['qty'],
            new Money($line['price'], $summary['currency'])
        ), $lines);

        return new Order($customer['name'], $taxId, $money, $items);
    }
}
