<?php declare(strict_types=1);

namespace FakerJson;

use ReflectionNamedType;
use ReflectionParameter;
use Webmozart\Assert\Assert;

class FakerFormatterParameterDefinition
{
    /**
     * @param ReflectionParameter $parameter
     */
    public function __construct(
        protected ReflectionParameter $parameter
    ) {
    }

    /**
     * serialize into array
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $result = [
            'name' => $this->parameter->getName(),
            'hasType' => $this->parameter->hasType(),
            'position' => $this->parameter->getPosition(),
            'isOptional' => $this->parameter->isOptional(),
            'isDefaultValueAvailable' => $this->parameter->isDefaultValueAvailable(),
        ];

        if ($this->parameter->isDefaultValueAvailable()) {
            $result['defaultValue'] = $this->parameter->getDefaultValue();
        }

        if ($this->parameter->hasType()) {
            $type = $this->parameter->getType();
            Assert::notNull($type);
            Assert::isInstanceOf($type, ReflectionNamedType::class);
            $result['type'] = $type->getName();
        }
        return $result;
    }
}
