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

        // testMethod1
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

        // testMethod2
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

        // testMethod3
        Assert::isArray($definitions[2]);
        Assert::isArray($definitions[2]['parameters']);
        Assert::isArray($definitions[2]['parameters'][0]);
        Assert::isArray($definitions[2]['parameters'][1]);
        $this->assertEquals('testMethod3', $definitions[2]['method']);
        $this->assertEquals('TestFakerProvider', $definitions[2]['provider']);
        $this->assertEquals('default', $definitions[2]['locale']);
        $this->assertEquals('param1', $definitions[2]['parameters'][0]['name']);
        $this->assertTrue($definitions[2]['parameters'][0]['has_type']);
        $this->assertEquals('string', $definitions[2]['parameters'][0]['type']);
        $this->assertEquals(['a'], $definitions[2]['parameters'][0]['expected_values']);
        $this->assertEquals('param2', $definitions[2]['parameters'][1]['name']);
        $this->assertTrue($definitions[2]['parameters'][1]['has_type']);
        $this->assertEquals('string', $definitions[2]['parameters'][1]['type']);
        $this->assertEquals(['a', 'b', 'c'], $definitions[2]['parameters'][1]['expected_values']);
        $this->assertEquals('param3', $definitions[2]['parameters'][2]['name']);
        $this->assertTrue($definitions[2]['parameters'][2]['has_type']);
        $this->assertEquals('number', $definitions[2]['parameters'][2]['type']);
        $this->assertEquals([1], $definitions[2]['parameters'][2]['expected_values']);
        $this->assertEquals('param4', $definitions[2]['parameters'][3]['name']);
        $this->assertTrue($definitions[2]['parameters'][3]['has_type']);
        $this->assertEquals('number', $definitions[2]['parameters'][3]['type']);
        $this->assertEquals([1, 2, 3], $definitions[2]['parameters'][3]['expected_values']);
        $this->assertEquals('param5', $definitions[2]['parameters'][4]['name']);
        $this->assertTrue($definitions[2]['parameters'][4]['has_type']);
        $this->assertEquals('number', $definitions[2]['parameters'][4]['type']);
        $this->assertEquals([], $definitions[2]['parameters'][4]['expected_values']);
    }
}
