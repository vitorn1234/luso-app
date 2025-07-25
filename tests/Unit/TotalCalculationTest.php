<?php

use App\Domain\Item;
use App\Domain\Money;
use App\Domain\Order;
use App\Domain\TaxId;
use App\Domain\User;
use App\Exceptions\LogicValidationException;

// repetitive use but might be needed if tested alone
$validatedData = [
    "customer_name" => "João Almeida",
    "customer_nif" => "12345678Z",
    "total" => "115.00",
    "currency" => "EUR",
    "items" => [
        [
            "sku" => "PEN-16GB",
            "qty" => 3,
            "unit_price" => "5.00",
        ],
        [
            "sku" => "NOTE-A5",
            "qty" => 10,
            "unit_price" => "10.00",
        ],
    ],
];

$taxId = new TaxId($validatedData['customer_nif']);
$user = new User($validatedData['customer_name'], $taxId);
$money = new Money($validatedData['total'], $validatedData['currency']);
$items = array();

foreach ($validatedData['items'] as $itemData) {
    $items[] = new Item($itemData['sku'], $itemData['qty'], $itemData['unit_price']);
}
$item = array($items[0]);

it('creates an order successfully with valid value', function ($user, $money, $items) {

// Mock or create your objects
    $this->expectNotToPerformAssertions(); // Avoid assertion failures on purpose

    $order = new Order($user, $money, $items); // This should not throw an exception
    $this->assertInstanceOf(Order::class, $order); // Verify an Order object is created

})->throwsNoExceptions()->with([
    [
        $user, // your user object
        $money, // your money object
        $items // your array of item objects
    ],
]);

it('fails to create an order with incorrect total value', function ($user, $money, $item) {

   new Order($user, $money, $item);
    //testing all possible ways of testing exception for future cases
//    expect(fn() => new Order($user, $money, $item))
//        ->toThrow(LogicValidationException::class);

})->throws(LogicValidationException::class,
    "Total 115.00 defined does not match the sum of the ordered items 15")
    ->with([
    [
        $user, // your user object
        $money, // your money object
        $item // your array of item Object - only one item object in array
    ],
]);
