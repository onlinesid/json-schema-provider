# json-schema-provider

Json Schema `Silex Service Provider` and validator tool

A Silex Service Provider for validating `JSON` data against a given `Schema`.

## Installation

### Library

    $ git clone https://github.com/onlinesid/json-schema-provider.git

### Dependencies

#### [`Composer`](https://github.com/composer/composer) (*will use the Composer ClassLoader*)

    $ wget http://getcomposer.org/composer.phar
    $ php composer.phar require onlinesid/json-schema-provider:dev-master

## Usage

#### Registering the Service Provider
```php
$app->register(new OnlineSid\Silex\Provider\JsonSchema\ServiceProvider(), array(
    'json-schema.options' => array(
        'json_schema_dir' => __DIR__.'/public/json-schema/',
        'json_message_dir' => __DIR__.'/public/json-message/',
        'default_json_message' => 'default.json',
    ),
));
```

- ``json_schema_dir``: where the json schema files are located
- ``json_message_dir``: where the json files for custom error messages are located
- ``default_json_message``: json file where default error messages are located, file must be in directory ``json_message_dir``

#### Usage in controller

```php
$validator = $this->app['json-schema-validator'];
$validation_result = $validator->validate(array(
    'request' => $this->request->get('booking'), // array to validate
    'json_schema' => 'booking.json', // under json_schema_dir
    'json_message' => 'booking.json', // under json_message_dir
));

// check $validation_result->isValid() to see if validation pass or fail
```
####json-schema/booking.json
See [json-schema](http://json-schema.org/) for more details.

This is your json schema containing constraints/rules
```json
{
  "type": "object",
  "properties": {
    "first_name": {
      "type": "string",
      "required": true,
      "maxLength": 100
    },
    "last_name": {
      "type": "string",
      "required": true,
      "maxLength": 100
    }
  }
}
```
####json-message/booking.json
You can specify your own custom error messages.
```json
{
  "first_name": {
    "label": "First name",
    "messages": {
      "required": "{{ label }} is required.",
      "maxLength": "{{ label }} must not be more than {{ schema.maxLength }} characters long."
    }
  },
  "last_name": {
    "label": "Last name",
    "messages": {
      "required": "{{ label }} is required.",
      "maxLength": "{{ label }} must not be more than {{ schema.maxLength }} characters long."
    }
  }
}
```
####json-message/default.json
You can specify global default error messages (e.g.: not per field but per constraint rule type)
```json
{
  "messages": {
    "required": "Required field.",
    "minLength": "Must be {{ schema.minLength }} chars or more.",
    "maxLength": "Must not be more than {{ schema.maxLength }} chars.",
    "pattern": "Incorrect format"
  }
}
```
## Running the tests

    $ phpunit
