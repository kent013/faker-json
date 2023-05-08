<?php declare(strict_types=1);

namespace Tests\Faker\Provider;

use DateTime;
use Faker\Provider\Base;

class TestFakerProvider extends Base
{
    /**
     * @param string $param1
     * @param string $param2
     * @param $param3
     * @param string $param4
     * @return string
     */
    public function testMethod1(string $param1, $param2, string $param3, ?string $param4): string
    {
        return 'test';
    }

    /**
     * @param $param1
     * @param string|int|float|array|DateTime|null $param2
     * @return string
     * @phpstan-ignore-next-line
     */
    public function testMethod2(string|int|float|array|DateTime|null $param1, $param2): string
    {
        return 'test';
    }

    /**
     * @param $param1 OneOf('a')
     * @param string $param2 OneOf('a', 'b','c' )
     * @param $param3 OneOf(1)
     * @param int $param4 OneOf(1, 2,3 )
     * @param int $param5
     * @return string
     */
    public function testMethod3(string $param1, $param2, int $param3, $param4, $param5): string
    {
        return 'test';
    }
}
