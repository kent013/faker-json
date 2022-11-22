# faker-json
Call [FakerPHP/Faker](https://github.com/FakerPHP/Faker) faker formatter with JSON.

The JSON is looks like,

```json
{
  "method": "numberBetween",
  "parameters": {
    "min": 20,
    "max": 30
  }
}
```

This json will call faker method as

```php
$faker = Faker\Factory::create();
$faker->numberBetween(20, 30);
```

# Installation

```
composer require kent013/faker-json
```

# Usage

## Call faker method

```php
// same as {"faker_json":true,"method":"numberBetween","parameters":{"min":20,"max":30}}

$json = FakerFormatter::instance()
    ->method('numberBetween')
    ->addParameter('min', 20)
    ->addParameter('max', 30)->toJson(); 
$fakerFormatter = FakerFormatter::fromJson($json);
$result = FakerJson::call($fakerFormatter);

// result is number between 20 and 30
```

## Call faker method with locale
```php
$fakerFormatter = FakerFormatter::instance()
    ->locale('en_HK')
    ->method('direction');
$result = FakerJson::call($fakerFormatter);
$this->assertIsString($result);
```

## get formatter definitions
as array

```php
FakerFormatterDefinition::listDefinitions()
```

as json

```php
FakerJson::formatterDefinitionsAsJson();
```

## get formatter locales
as array

```php
FakerFormatterDefinition::listLocales()
```

as json
```php
FakerJson::formatterLocalesAsJson();
```
