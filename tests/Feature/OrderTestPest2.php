<?php

beforeEach(function () {
    $this->jsonData = [
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

    $this->jsonDataV2 = [
        "data" => [
            "type" => "orders",
            "attributes" => [
                "customer" => [
                    "name" => "João Almeida",
                    "nif" => "12345678Z"
                ],
                "summary" => [
                    "currency" => "EUR",
                    "total" => "115.00"
                ],
                "lines" => [
                    [
                        "sku" => "PEN-16GB",
                        "qty" => 3,
                        "price" => "5.00"
                    ],
                    [
                        "sku" => "NOTE-A5",
                        "qty" => 10,
                        "price" => "10.00"
                    ]
                ]
            ]
        ]
    ];
});

it('returns a successful response for the home route', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

it('creates an order v1 successfully', function () {
    $response = $this->post('/api/v1/order', $this->jsonData);
    $response->assertCreated(); // 201
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
});

it('fails order creation v1 with invalid request body', function () {
    // Missing total
    $jsonData = [
        "customer_name" => "João Almeida",
        "customer_nif" => "12345678Z",
        "currency" => "EUR"
    ];
    $response = $this->post('/api/v1/order', $jsonData);
    $response->assertStatus(422);
    $response->assertJson([
        "error" => "Failed processing body",
        "message" => "The total field is required. (and 1 more error)"
    ]);
});
it('fails order creation v1 with invalid total', function () {
    // Total mismatch
    $this->jsonData['total'] = "145.00";
    $response = $this->post('/api/v1/order', $this->jsonData);
    $response->assertStatus(422);
    $response->assertJson([
        "error" => "Failed processing body",
        'message' => 'Total 145.00 defined does not match the sum of the ordered items 115',
    ]);
    $this->jsonData['total'] = "115.00";
});
it('fails order creation v1 with invalid currency', function () {
    // Invalid currency
    $this->jsonData['currency'] = "USD";
    $response = $this->post('/api/v1/order', $this->jsonData);
    $response->assertStatus(422);
    $response->assertJson([
        "error" => "Failed processing body",
        'message' => 'The selected currency is invalid.',
    ]);
    $this->jsonData['currency'] = "EUR";
});
it('fails order creation v1 v2 with no items', function () {
    // Empty items array
    $this->jsonData['items'] = [];
    $response = $this->post('/api/v1/order', $this->jsonData);
    $response->assertStatus(422);
    $response->assertJson([
        "error" => "Failed processing body",
        'message' => 'The items field is required.',
    ]);
});

it('creates an order v2 successfully', function () {
    $response = $this->post('/api/v2/order', $this->jsonDataV2);
    $response->assertCreated(); // 201
    $response->assertJsonStructure([
        "links" => [
            "self"
        ],
        "data" => [
            "type",
            "id",
            "attributes" => [
                "uuid",
                "status",
                "currency",
                "total",
                "created_at",
            ]
        ]
    ]);
    // Check specific JSON paths
    $response->assertJsonPath('data.type', 'orders');
    $response->assertJsonPath('data.attributes.currency', 'EUR');

    // Validate date format
    $response->assertJsonPath('data.attributes.created_at', function ($dateString) {
        $format = 'Y-m-d\TH:i:s\Z';
        $date = \DateTime::createFromFormat($format, $dateString);
        return $date !== false && $date->format($format) === $dateString;
    });
});

it('fails order creation v2 with invalid request body', function () {
    // Missing total
    $jsonData = [
        "customer_name" => "João Almeida",
        "customer_nif" => "12345678Z",
        "currency" => "EUR"
    ];
    $response = $this->post('/api/v2/order', $jsonData);
    $response->assertStatus(422);
    $response->assertJson([
        "errors" => [
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/type"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The data.type field is required."
            ],
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/customer/name"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The data.attributes.customer.name field is required."
            ],
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/customer/nif"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The data.attributes.customer.nif field is required."
            ],
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/summary/currency"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The data.attributes.summary.currency field is required."
            ],
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/summary/total"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The data.attributes.summary.total field is required."
            ],
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/lines"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The data.attributes.lines field is required."
            ],
        ],
    ]);
});

it('fails order creation v2 with invalid total', function () {
    // Total mismatch
    $this->jsonDataV2["data"]["attributes"]["summary"]['total'] = "145.00";
    $response = $this->post('/api/v2/order', $this->jsonDataV2);
    $response->assertStatus(422);
    $response->assertJson([
        "errors" => [
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/summary/total"
                ],
                "title" => "Invalid Attribute",
                "detail" => "Total 145.00 defined does not match the sum of the ordered items 115"
            ]
        ]
    ]);
//    $this->jsonData['total'] = "115.00";
});

it('fails order creation v2 with invalid currency', function () {
    // Invalid currency
    $this->jsonDataV2["data"]["attributes"]["summary"]['currency'] = "USD";
    $response = $this->post('/api/v2/order', $this->jsonDataV2);
    $response->assertStatus(422);
    $response->assertJson([
        "errors" => [
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/summary/currency"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The selected data.attributes.summary.currency is invalid."
            ]
        ]
    ]);
//    $this->jsonData['currency'] = "EUR";
});

it('fails order creation v2 with no items', function () {
    // Empty items array
    $this->jsonDataV2["data"]["attributes"]['lines'] = [];
    $response = $this->post('/api/v2/order', $this->jsonDataV2);
    $response->assertStatus(422);
    $response->assertJson([
        "errors" => [
            [
                "status" => 422,
                "source" => [
                    "pointer" => "/data/attributes/lines"
                ],
                "title" => "Invalid Attribute",
                "detail" => "The data.attributes.lines field is required."
            ]
        ]
    ]);
});
