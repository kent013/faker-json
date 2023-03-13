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

## Load third party providers

To load third party providers to generate value, call addProvider prior to use the provider.

```php
FakerJson::addProvider(PHPFakerUtil::class);
```

Especially in Laravel, create `FakerJsonServiceProvider` and add it to `app.provider` config

```php
<?php declare(strict_types=1);

namespace App\Providers;

use Faker\Provider\PHPFakerUtil;
use FakerJson\FakerJson;
use Illuminate\Support\ServiceProvider;

class FakerJsonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        FakerJson::addProvider(PHPFakerUtil::class); 
    }
}
```

config/app.php

```php
'providers' => [
    // ...
    App\Providers\FakerJsonServiceProvider::class,
],
```


