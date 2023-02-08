<?php declare(strict_types=1);

namespace FakerJson;

use Faker\Extension\ExtensionNotFound;
use Faker\Generator;
use InvalidArgumentException;
use ReflectionMethod;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException as AssertInvalidArgumentException;

class FakerProxy
{
    public function __construct(
        protected Generator $faker
    ) {
    }

    /**
     * Proxy faker method call.
     * @param FakerFormatter $fakerFormatter
     * @return mixed
     * @throws AssertInvalidArgumentException
     * @throws ExtensionNotFound
     * @throws InvalidArgumentException
     */
    public function call(FakerFormatter $fakerFormatter): mixed
    {
        Assert::string($fakerFormatter->method);
        $formatter = $this->getFormatter($fakerFormatter->method);

        // @phpstan-ignore-next-line
        $method = new ReflectionMethod($formatter[0], $formatter[1]);
        $methodParameters = $method->getParameters();
        $params = [];

        foreach ($fakerFormatter->parameters as $key => $value) {
            $found = false;

            foreach ($methodParameters as $methodParameter) {
                if ($methodParameter->name == $key) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new InvalidArgumentException("property {$key} is not exists in {$fakerFormatter->method} formatter");
            }

            if ($value instanceof FakerFormatter) {
                $params[$key] = self::call($value);
            } else {
                $params[$key] = $value;
            }
        }
        return call_user_func_array($formatter, $params);
    }

    /**
     * Returns a random number between $int1 and $int2 (any order)
     *
     * @example 79907610
     * @param int $min
     * @param int $max
     */
    public function numberBetween(int $min = 0, int $max = 2147483647): int
    {
        return $this->faker->numberBetween($min, $max);
    }

    /**
     * get proxy method of formatter
     * @param string $method
     * @return callable
     * @throws ExtensionNotFound
     * @throws InvalidArgumentException
     */
    protected function getFormatter(string $method): callable
    {
        if (in_array($method, ['listFormats']) || str_starts_with($method, '__')) {
            throw new InvalidArgumentException('Magic methods or other util methods are not accessible.');
        }

        if (method_exists($this, $method)) {
            // @phpstan-ignore-next-line
            return [$this, $method];
        }

        if (method_exists($this->faker, $method)) {
            // @phpstan-ignore-next-line
            return [$this->faker, $method];
        }
        return $this->faker->getFormatter($method);
    }
}
