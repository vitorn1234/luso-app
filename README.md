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

## Input structure

For this i decided to follow the validator already proven return structure after validating properly,so we have 2
validator structure that share the validations but create diferent Objects that we process and transfer to the Order
DTO.

## Output structure

Since we are not using DB I simply created a class that receives the Domain object(DTO?) and processes it into the
response type we require,
may it be v1 or v2 with each particular specifications

### Version Selection & Serialization
Since I wasn't sure what was expected, I initially followed a standard approach. Then, I used a controller to process the data, applying different logic based on the version to ensure proper handling.
### Feature Testing:

- Validating v1 200
    - Expect value Fields
    - Expected field struct : EUR/ datetime
- Validating v1 error cases
- Validating v2
- Validating v2 error cases

Both cases exhibit similar error patterns,
but the way the response is handled differs.

### Unit Testing


## Non-Functional Requirements
FOR psr-12 I installed composer require --dev squizlabs/php_codesniffer for validating
vendor/bin/phpcs --standard=PSR12 app/Http/Controllers
"scripts": {
"lint": "phpcs --standard=PSR12 app/",
"fix": "phpcbf --standard=PSR12 app/"
} to composer
