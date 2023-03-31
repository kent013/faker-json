<?php declare(strict_types=1);

namespace Tests\Faker\Provider;

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
        return $param1 . $param2 . $param3 . ($param4 ?? '');
    }
}
