<?php declare(strict_types=1);

namespace FakerJson;

use FakerJson\Exception\FakerFormatterParameterNotFoundException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException as AssertInvalidArgumentException;

/**
 * @property bool $fakerJson
 * @property string|null $locale
 * @property string|null $method
 * @property array<string,mixed> $parameters
 */
class FakerFormatter
{
    /**
     * @param string|null $locale
     * @param string|null $method
     * @param array<string,mixed> $parameters
     */
    public function __construct(
        protected ?string $locale = null,
        protected ?string $method = null,
        protected array $parameters = [],
    ) {
    }

    /**
     * get property.
     *
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __get(string $name): mixed
    {
        Assert::propertyExists($this, $name);
        return $this->{$name};
    }

    /**
     * get instance.
     *
     * @return FakerFormatter
     */
    public static function instance(): self
    {
        return new self();
    }

    /**
     * create instance from json.
     *
     * @param string $json
     *
     * @return FakerFormatter
     */
    public static function fromJson(string $json): self
    {
        $array = json_decode($json, true);
        Assert::isArray($array);

        return self::fromArray($array);
    }

    /**
     * create instance from array.
     *
     * @param array<string,mixed> $array
     *
     * @return FakerFormatter
     */
    public static function fromArray(array $array): self
    {
        if (!self::isFakerJsonArray($array)) {
            throw new InvalidArgumentException('array index method must exist');
        }

        $locale = null;

        if (isset($array['locale'])) {
            $locale = $array['locale'];
            Assert::string($locale);
        }

        $method = $array['method'];
        Assert::string($method);

        $instance = new self(
            locale: $locale,
            method: $method,
        );

        $parameters = $array['parameters'] ?? [];

        if (!empty($parameters)) {
            Assert::isIterable($parameters);

            foreach ($parameters as $value) {
                if (isset($value['faker_definition']) && is_array($value['faker_definition']) && self::isFakerJsonArray($value['faker_definition'])) {
                    $instance->addParameter($value['name'], self::fromArray($value['faker_definition']));
                } else {
                    $instance->addParameter($value['name'], $value['value']);
                }
            }
        }

        return $instance;
    }

    /**
     * @param array<string,mixed> $array
     * @return bool
     */
    public static function isFakerJsonArray(array $array): bool
    {
        return isset($array['method']);
    }

    /**
     * set locale.
     *
     * @return FakerFormatter
     * @param string $locale
     */
    public function locale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * set method.
     *
     * @return FakerFormatter
     * @param string $method
     */
    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * add parameter.
     *
     * @return FakerFormatter
     * @param string $name
     * @param mixed $value
     */
    public function addParameter(string $name, mixed $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * remove parameter.
     *
     * @return FakerFormatter
     * @param string $name
     */
    public function removeParameter(string $name): self
    {
        unset($this->parameters[$name]);

        return $this;
    }

    /**
     * clear parameters.
     *
     * @return FakerFormatter
     */
    public function clearparameters(): self
    {
        $this->parameters = [];

        return $this;
    }

    /**
     * get parameter.
     * @param string $name
     */
    public function parameter(string $name): mixed
    {
        if (!isset($this->parameters[$name])) {
            throw new FakerFormatterParameterNotFoundException($this->method ?? '', $name);
        }

        return $this->parameters[$name];
    }

    /**
     * @param string $name
     * @return int
     * @throws InvalidArgumentException
     * @throws AssertInvalidArgumentException
     */
    public function intParameter(string $name): int
    {
        $parameter = $this->parameter($name);
        Assert::integer($parameter);

        return $parameter;
    }

    /**
     * @param string $name
     * @return FakerFormatter
     * @throws InvalidArgumentException
     * @throws AssertInvalidArgumentException
     */
    public function fakerFormatterParameter(string $name): self
    {
        $parameter = $this->parameter($name);
        Assert::isInstanceOf($parameter, self::class);

        return $parameter;
    }

    /**
     * serialize instance to array.
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $result = [
            'method' => $this->method,
        ];

        if (!is_null($this->locale)) {
            $result['locale'] = $this->locale;
        }

        if (!empty($this->parameters)) {
            $parameters = [];

            foreach ($this->parameters as $key => $value) {
                if ($value instanceof self) {
                    $parameters[] = ['name' => $key, 'use_faker' => true, 'faker_definition' => $value->toArray()];
                } else {
                    $parameters[] = ['name' => $key, 'use_faker' => false, 'value' => $value];
                }
            }
            $result['parameters'] = $parameters;
        }

        return $result;
    }

    /**
     * serialize instance to json.
     * @return string
     */
    public function toJson(): string
    {
        $result = json_encode($this->toArray());
        Assert::string($result);
        return $result;
    }
}
