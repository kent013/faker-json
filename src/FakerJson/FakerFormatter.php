<?php declare(strict_types=1);

namespace FakerJson;

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
     * @param bool $fakerJson
     * @param string|null $locale
     * @param string|null $method
     * @param array<string,mixed> $parameters
     */
    public function __construct(
        protected bool $fakerJson = true,
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
        if (!isset($array['method'])) {
            throw new InvalidArgumentException('array index method must exist');
        }

        $fakerJson = false;

        if (isset($array['faker_json'])) {
            $fakerJson = (bool) $array['faker_json'];
        }

        $locale = null;

        if (isset($array['locale'])) {
            $locale = $array['locale'];
            Assert::string($locale);
        }

        $method = $array['method'];
        Assert::string($method);

        $instance = new self(
            fakerJson: $fakerJson,
            locale: $locale,
            method: $method,
        );

        $parameters = $array['parameters'] ?? [];

        if (!empty($parameters)) {
            Assert::isIterable($parameters);

            foreach ($parameters as $key => $value) {
                if ($value['faker_json'] ?? false) {
                    $instance->addParameter($key, self::fromArray($value));
                } else {
                    $instance->addParameter($key, $value);
                }
            }
        }

        return $instance;
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
            throw new InvalidArgumentException("parameters ${name} is not exist");
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
            'faker_json' => $this->fakerJson,
            'method' => $this->method,
        ];

        if (!is_null($this->locale)) {
            $result['locale'] = $this->locale;
        }

        if (!empty($this->parameters)) {
            $parameters = [];

            foreach ($this->parameters as $key => $value) {
                if ($value instanceof self) {
                    $parameters[$key] = $value->toArray();
                } else {
                    $parameters[$key] = $value;
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
