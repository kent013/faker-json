<?php declare(strict_types=1);

namespace FakerJson;

use Faker;
use Faker\Generator;
use ReflectionClass;
use ReflectionException;
use Webmozart\Assert\Assert;

class FakerJson
{
    /**
     * provider classes
     * @var array<class-string>
     */
    protected static array $providers = [];

    public static function call(FakerFormatter $fakerFormatter): mixed
    {
        $faker = is_null($fakerFormatter->locale) ? Faker\Factory::create() : Faker\Factory::create($fakerFormatter->locale);
        self::loadProviders($faker);
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

    /**
     * @param class-string $class
     */
    public static function addProvider(string $class): void
    {
        self::$providers[$class] = $class;
    }

    /**
     * load Providers
     * @param Generator $generator
     * @throws ReflectionException
     */
    public static function loadProviders(Generator $generator): void
    {
        foreach (self::$providers as $provider) {
            $kllas = new ReflectionClass($provider);
            $provider = $kllas->newInstanceArgs([$generator]);
            $generator->addProvider($provider);
        }
    }
}
