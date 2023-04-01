<?php
declare(strict_types=1);

namespace Tests\Unit\FakerJson;

use FakerJson\FakerFormatterDefinition;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class FakerFormatterDefinitionTest extends TestCase
{
    /**
     * test get formatter definition
     */
    public function testGetFormatterDefinitionsFromClassname(): void
    {
        $definitions = FakerFormatterDefinition::getFormatterDefinitionsFromClassname('\Tests\Faker\Provider\TestFakerProvider');
        $definitions = array_values($definitions);
        Assert::isArray($definitions[0]);
        Assert::isArray($definitions[0]['parameters']);
        Assert::isArray($definitions[0]['parameters'][0]);
        Assert::isArray($definitions[0]['parameters'][1]);
        Assert::isArray($definitions[0]['parameters'][2]);
        $this->assertEquals('testMethod1', $definitions[0]['method']);
        $this->assertEquals('TestFakerProvider', $definitions[0]['provider']);
        $this->assertEquals('default', $definitions[0]['locale']);
        $this->assertEquals('param1', $definitions[0]['parameters'][0]['name']);
        $this->assertTrue($definitions[0]['parameters'][0]['has_type']);
        $this->assertEquals('string', $definitions[0]['parameters'][0]['type']);
        $this->assertEquals('param2', $definitions[0]['parameters'][1]['name']);
        $this->assertTrue($definitions[0]['parameters'][1]['has_type']);
        $this->assertEquals('string', $definitions[0]['parameters'][1]['type']);
        $this->assertEquals('param3', $definitions[0]['parameters'][2]['name']);
        $this->assertTrue($definitions[0]['parameters'][2]['has_type']);
        $this->assertEquals('string', $definitions[0]['parameters'][2]['type']);

        Assert::isArray($definitions[1]);
        Assert::isArray($definitions[1]['parameters']);
        Assert::isArray($definitions[1]['parameters'][0]);
        Assert::isArray($definitions[1]['parameters'][1]);
        $this->assertEquals('testMethod2', $definitions[1]['method']);
        $this->assertEquals('TestFakerProvider', $definitions[1]['provider']);
        $this->assertEquals('default', $definitions[1]['locale']);
        // int / float is converted into number and unified
        // null will removed
        $this->assertEquals('param1', $definitions[1]['parameters'][0]['name']);
        $this->assertTrue($definitions[1]['parameters'][0]['has_type']);
        $this->assertEquals('DateTime,array,number,string', $definitions[1]['parameters'][0]['type']);
        $this->assertEquals('param2', $definitions[1]['parameters'][1]['name']);
        $this->assertTrue($definitions[1]['parameters'][1]['has_type']);
        $this->assertEquals('DateTime,array,number,string', $definitions[1]['parameters'][1]['type']);
    }
}
