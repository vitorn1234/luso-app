<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderTest extends TestCase
{
    public $jsonData = [
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
    public $jsonDataV2 = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => [
                    "name" => "João Almeida",
                    "nif" => "12345678Z"
                ],
                "summary" => [
                    "currency" => "EUR",
                    "total" => "199.90"
                ],
                "lines" => [
                    [
                        "sku" => "PEN-16GB",
                        "qty" => 3,
                        "price" => "9.90"
                    ],
                    [
                        "sku" => "NOTE-A5",
                        "qty" => 10,
                        "price" => "12.00"
                    ]
                ]
            ]
        ]
    ];
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_order_creation_v1(): void
    {
        $response = $this->post('/api/v1/order', $this->jsonData);
        $response->assertCreated(); // Checks for 201 status
        // Assert the response JSON structure
        $response->assertJsonStructure([
            'data' => [
                'uuid',
                'number',
                'status',
                'total',
                'currency',
                'created_at',
            ],
        ]);
    }

    public function test_order_creation_v1_fail_request_body(): void
    {
        $jsonData = [
            "customer_name" => "João Almeida",
            "customer_nif" => "12345678Z",
            "currency" => "EUR"
        ];

        $response = $this->post('/api/v1/order', $jsonData);
        $response->assertBadRequest(); // Checks for 201 status
        // Assert the response JSON structure
        $response->assertJson([
            "error" => "Failed processing body",
            "message" => "The total field is required. (and 1 more error)"
        ]);


        $this->jsonData['total'] = "145.00";
        $response = $this->post('/api/v1/order', $this->jsonData);
        $response->assertBadRequest(); // Checks for 400 status
        // Assert the response JSON structure
        $response->assertJson([
            "error" => "Failed processing body",
            'message' => 'Total 145.00 defined does not match the sum of the ordered items 115',
        ]);
        $this->jsonData['total'] = "115.00";

        // validate currency
        $this->jsonData['currency'] = "USD";
        $response = $this->post('/api/v1/order', $this->jsonData);
        $response->assertBadRequest(); // Checks for 400 status
        // Assert the response JSON structure
        $response->assertJson([
            "error" => "Failed processing body",
            'message' => 'The selected currency is invalid.',
        ]);
        $this->jsonData['currency'] = "EUR";

        // validate items should be last :D
        $this->jsonData['items'] = [];
        $response = $this->post('/api/v1/order', $this->jsonData);
        $response->assertBadRequest(); // Checks for 400 status
        // Assert the response JSON structure
        $response->assertJson([
            "error" => "Failed processing body",
            'message' => 'The items field is required.',
        ]);

    }

//    public function test_order_get_v1(): void
//    {
//        // Step 1: Fetch existing order - Assert 200 OK
//        $response = $this->get('/api/v1/order/e3d4b1d2-97db-4e5d-a7f5-7c9f7b1c2e10');
//        $response->assertOk(); // Asserts status 200
//    }

    public function test_order_creation_v2(): void
    {
        $response = $this->post('/api/v2/order', $this->jsonDataV2);
        $response->assertCreated(); // Checks for 201 status
        // Assert the response JSON structure
        $response->assertJsonStructure([
            "links" => [
                "self"
            ],
            "data" => [
                "type" ,
                "id" ,
                "attributes" => [
                    "uuid" ,
                    "status" ,
                    "currency" ,
                    "total" ,
                    "created_at" ,
                ]
            ]
        ]);

        // Check for type
//        $response->assertJsonFragment(['data' => ['type' => 'orders']]); // not working
        $response->assertJsonPath('data.type', 'orders');

        $response->assertJsonPath('data.attributes.currency', 'EUR');


        $response->assertJsonPath('data.attributes.created_at', function ($dateString){
            // Using DateTime for robust date validation
            $format = 'Y-m-d\TH:i:s\Z';
            $date = \DateTime::createFromFormat($format, $dateString);

            // Check if the creation was successful.  Important!
            if ($date === false) {
                return false;
            }

            return $date->format($format) === $dateString;
        });
    }
}
