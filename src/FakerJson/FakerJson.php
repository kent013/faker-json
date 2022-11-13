<?php declare(strict_types=1);

namespace FakerJson;

use Faker;
use Webmozart\Assert\Assert;

class FakerJson
{
    public static function call(FakerFormatter $fakerFormatter): mixed
    {
        $faker = is_null($fakerFormatter->locale) ? Faker\Factory::create() : Faker\Factory::create($fakerFormatter->locale);
        $proxy = new FakerProxy($faker);
        return $proxy->call($fakerFormatter);
    }

    public static function formatterDefinitionsAsJson(): string
    {
        $json = json_encode(FakerFormatterDefinition::listDefinitions());
        Assert::string($json);
        return $json;
    }

    public static function formatterLocalesAsJson(): string
    {
        $json = json_encode(FakerFormatterDefinition::listLocales());
        Assert::string($json);
        return $json;
    }
}
