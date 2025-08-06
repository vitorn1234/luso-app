Tools:
https://www.jetbrains.com/phpstorm/
https://deepai.org/chat/ai-code

### Domain Modelling

Inside App/Domain create a few classes:

- Order(class) ()- recieves a user, the money used and an array of Items
    - User(class) (name + TaxId(class))- has a name and his TaxId (NIF value and validating NIF)
    - Money(class) (amount + currency) - here we have the validating field amount and currency
    - []Item ( sku, qty, price) - here we validate the qty (int) and the price structure to see if it's a valid float
      inside a string
      I used the fields of the json to define most values

If we try to create a new Order(User,Money,[]Item), all the user data money and item should be validated when creating
they respective object
but we validate the Order total since the sum of values of the items should be correct, If it fails it should return a
throw error, this should be validated already when validating the body but jsut in case. If the case happends we need to
process that error into a response error inside the controller

This decision were basically based on what was written in the doc.

Besides this structure I did a Model for the DB, a simple one but after reading the Challenge it wasn't really what was
asked but I left it.

### Application Layer
Get input and version, validates and return the data in a readable format
`$validatedData = $request->validate(AppHelper::getValidatorVersion($apiVersion));`

Save the data into the service object.
`$service = new Service($apiVersion,$validatedData);`

Process the data based on the version into the lets call it DTO Order Object. Using the processOrder() which adds the fields missing
`$service = $service->processOrder()`

then we can send to an external service using POST currently (it will fail cause the url was not define 
and it uses the default),
`->sendExternalService();`

also we can change the version to another we can receive v1 and send it like v2
`->setVersion('v2');`

if the service sent data to the external service, `$service->sent ` will be true 

Then we have a Resource which basicly transforms the data to what we expect to be the response, we have toArray and response to be json
`(new OrdersResource($apiVersion,$service->getOrder()))->response();`

Could have organized better the locations of certain part of the code like using the Order class to group everything related to Order data processing/validated and responding as static or in other ways

But have a AppHelper for Error responses, could have changed it to ErrorHAndler, 
have OrderResource to have all processing of the response with uses the Order object.

## Input structure
Using validator to define/validate the input data and the initial structure to pass to the Order object
For this i decided to follow the validator already proven return structure after validating properly,so we have 2
validator structure that share the validations but create different Objects that we process and transfer to the Order
DTO.

## Output structure
Mainly in OrderResource
Since we are not using DB I simply created a class that receives the Domain object(DTO?) and processes it into the
response type we require,
may it be v1 or v2 with each particular specifications

### Version Selection & Serialization
Since I wasn't sure what was expected, I initially followed a standard approach. Then, I used a controller to process the data, applying different logic based on the version to ensure proper handling.
### Feature Testing:
./vendor/bin/pest to test

* Validating v1 201
    - Expect value Fields
    - Expected field struct : EUR/ datetime
* Validating v1 error cases 422
  * Body errors like wrong values
  * Validating calculations (left validating total calculations inside the object creation)
  * Validating NIF
* Validating v2 201
  * Valid case
* Validating v2 error cases 422
  * Body errors like wrong values 
  * Validating calculations (left validating total calculations inside the object creation)
  * Validating NIF
Missing weird cases
  * 
in case it does not compute/show ./vendor/bin/pest tests/Feature/OrderTestPest.php comand line inside project
```
   PASS  Tests\Feature\OrderTestPest
  ✓ it returns a successful response for the home route                                                                                                                                                                                                                0.21s  
  ✓ it creates an order v1 successfully                                                                                                                                                                                                                                0.91s  
  ✓ it fails order creation v1 with invalid request body                                                                                                                                                                                                               0.02s  
  ✓ it fails order creation v1 with invalid total                                                                                                                                                                                                                      0.01s  
  ✓ it fails order creation v1 with invalid currency                                                                                                                                                                                                                   0.02s  
  ✓ it fails order creation v1 v2 with no items                                                                                                                                                                                                                        0.02s  
  ✓ it creates an order v2 successfully                                                                                                                                                                                                                                0.76s  
  ✓ it fails order creation v2 with invalid request body                                                                                                                                                                                                               0.02s  
  ✓ it fails order creation v2 with invalid total                                                                                                                                                                                                                      0.02s  
  ✓ it fails order creation v2 with invalid currency                                                                                                                                                                                                                   0.02s  
  ✓ it fails order creation v2 with no items   
```
### Unit Testing
* total calculations - TotalCalculationTest.php
  * create Order with correct values and total
  * create Order with one missing item
  * 
* request construction - RequestTest.php, the feature testing does all the heavy lifting here
  * v1 - request data -> Order object and compare
  * v2 - request data -> Order object and compare
* response parsing -  ResponseParsingTest.php
  * v1 response parsing test
  * v2 response parsing test
    PASS  Tests\Unit\RequestTest
    ✓ it asserts that true is true                                                                                                                                                                                                                                       0.01s

```  PASS  Tests\Unit\ResponseParsingTest
  ✓ it v1 response parsing test with (App\Domain\Order, [['573bbffe-154f-473a-9ac0-3539a93158aa', 'ORD-2025-53026', 'created', …]])                                                                                                                                    0.01s  
  ✓ it v2 response parsing test with (App\Domain\Order, [['https://micros.services/api/v…-53026'], ['orders', 'ORD-2025-53026', ['573bbffe-154f-473a-9ac0-3539a93158aa', 'created', 'EUR', …]]])

  WARN  Tests\Unit\TotalCalculationTest
  ! it creates an order successfully with valid value with (App\Domain\User, App\Domain\Money, [App\Domain\Item, App\Domain\Item]) → This test is not expected to perform assertions but performed 1 assertion                                                         0.01s  
  ✓ it fails to create an order with incorrect total value with (App\Domain\User, App\Domain\Money, [App\Domain\Item])
```
## Non-Functional Requirements
FOR psr-12 I installed composer require --dev squizlabs/php_codesniffer for validating
vendor/bin/phpcs --standard=PSR12 app/Http/Controllers
`"scripts": {
"lint": "phpcs --standard=PSR12 app/",
"fix": "phpcbf --standard=PSR12 app/"
} `
to composer

### Final ideas
Could make the transformation better it would make it harder to understand request -> object -> response

There can be some issues testing specially with the float decimal part
And any other test cases I didn't test

Could have create a function to do the calculations instead of doing it automatically
like ->calculateTotal or ->validateTotal()... options.... making it easier to test calculation

There are 3 OrderController the correct one is the one inside Controller root, the other 2 was a test
without reading the doc :D

### GIT
https://github.com/vitorn1234/luso-app



## REDO

1. Make data + validations
2. Create Request data v1 or v2 using a factory like
3. Midgame create Order object and use that to create the request data
4. Make request to api v1 or v2
5. Process Response using a factory like
6. Make validations (ignore, process only uiid or id based on version)


Principles Applied

SOLID: Single Responsibility (DTOs, services), Dependency Inversion (factory pattern).
DRY: Centralized serialization/deserialization.
PSR-12: Consistent code style. (autoamtic)
Clean Architecture: Domain, Application, Infrastructure layers separated.


