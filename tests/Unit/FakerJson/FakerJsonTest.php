<?php
declare(strict_types=1);

namespace Tests\Unit\FakerJson;

use FakerJson\FakerFormatter;
use FakerJson\FakerJson;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class FakerJsonTest extends TestCase
{
    /**
     * test method call serialization
     */
    public function testFakerFormatterSerialization(): void
    {
        $fakerFormatter = $this->getFakerFormatter();
        $json = $fakerFormatter->toJson();
        $deserializedFakerFormatter = FakerFormatter::fromJson($json);

        $this->assertEquals($fakerFormatter->method, $deserializedFakerFormatter->method);
        $this->assertEquals($fakerFormatter->locale, $deserializedFakerFormatter->locale);
        $this->assertEquals(
            $fakerFormatter->fakerFormatterParameter('maxNbChars')->method,
            $deserializedFakerFormatter->fakerFormatterParameter('maxNbChars')->method
        );
        $this->assertEquals(
            $fakerFormatter->fakerFormatterParameter('maxNbChars')->fakerFormatterParameter('min')->method,
            $deserializedFakerFormatter->fakerFormatterParameter('maxNbChars')->fakerFormatterParameter('min')->method
        );
    }

    public function testCallFaker(): void
    {
        $fakerFormatter = $this->getFakerFormatter();
        $json = $fakerFormatter->toJson();
        $deserializedFakerFormatter = FakerFormatter::fromJson($json);
        $result = FakerJson::call($deserializedFakerFormatter);
        Assert::string($result);
        $this->assertGreaterThanOrEqual($deserializedFakerFormatter->fakerFormatterParameter('maxNbChars')->fakerFormatterParameter('min')->intParameter('min'), mb_strlen($result));
        $this->assertLessThanOrEqual($deserializedFakerFormatter->fakerFormatterParameter('maxNbChars')->intParameter('max'), mb_strlen($result));
    }

    public function testCallLocaleFaker(): void
    {
        $fakerFormatter = FakerFormatter::instance()
            ->locale('en_HK')
            ->method('direction');
        $result = FakerJson::call($fakerFormatter);
        $this->assertIsString($result);
    }

    public function testListDefinitions(): void
    {
        $definitions = FakerJson::formatterDefinitionsAsJson();
        $definitions = json_decode($definitions, true);
        $this->assertIsArray($definitions);
        $this->assertTrue(isset($definitions[0]['method']));
    }

    public function testListLocales(): void
    {
        $locales = FakerJson::formatterLocalesAsJson();
        $locales = json_decode($locales, true);
        $this->assertIsArray($locales);
        $this->assertContains('en_US', $locales);
        $this->assertContains('ja_JP', $locales);
    }

    protected function getFakerFormatter(): FakerFormatter
    {
        return FakerFormatter::instance()
            ->locale('ja_JP')
            ->method('realText')
            ->addParameter(
                'maxNbChars',
                FakerFormatter::instance()
                    ->method('numberBetween')
                    ->addParameter(
                        'min',
                        FakerFormatter::instance()
                            ->method('numberBetween')
                            ->addParameter('min', 20)
                            ->addParameter('max', 30)
                    )
                    ->addParameter('max', 50)
            );
    }
}
