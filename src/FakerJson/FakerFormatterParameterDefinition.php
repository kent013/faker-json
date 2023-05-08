<?php declare(strict_types=1);

namespace FakerJson;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypelessParamTagValueNode;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Webmozart\Assert\Assert;

class FakerFormatterParameterDefinition
{
    /**
     * @param ReflectionParameter $parameter
     * @param ParamTagValueNode|TypelessParamTagValueNode|null $parameterDocCommentNode
     */
    public function __construct(
        protected ReflectionParameter $parameter,
        protected ParamTagValueNode|TypelessParamTagValueNode|null $parameterDocCommentNode,
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
            'position' => $this->parameter->getPosition(),
            'is_optional' => $this->parameter->isOptional(),
            'is_default_value_available' => $this->parameter->isDefaultValueAvailable(),
            'has_type' => false,
            'expected_values' => [],
        ];

        if ($this->parameter->isDefaultValueAvailable()) {
            $defaultValue = $this->parameter->getDefaultValue();

            if (is_array($defaultValue)) {
                $result['default_value'] = implode(',', $defaultValue);
            } else {
                // @phpstan-ignore-next-line
                $result['default_value'] = (string) $defaultValue;
            }
        }

        if ($this->parameter->hasType()) {
            $type = $this->parameter->getType();
            Assert::notNull($type);
            $result['has_type'] = true;

            if ($type instanceof ReflectionNamedType) {
                $result['type'] = FakerFormatterDefinition::convertType($type->getName());
            } else {
                Assert::isInstanceOf($type, ReflectionUnionType::class);
                $types = [];

                foreach ($type->getTypes() as $type) {
                    $types[] = $type->getName();
                }
                $result['type'] = FakerFormatterDefinition::convertTypes($types);
            }
        } elseif ($this->parameterDocCommentNode && $this->parameterDocCommentNode instanceof ParamTagValueNode) {
            $result['has_type'] = true;
            $result['type'] = FakerFormatterDefinition::convertTypeNodeToString($this->parameterDocCommentNode->type);
        }

        if ($this->parameterDocCommentNode && preg_match('/OneOf\((.+?)\)/', $this->parameterDocCommentNode->description, $matches)) {
            $parsedValues = explode(',', $matches[1]);

            foreach ($parsedValues as $parsedValue) {
                $value = trim($parsedValue, " '");

                if (isset($result['type']) && $result['type'] === 'number') {
                    $result['expected_values'][] = (int) $value;
                } else {
                    $result['expected_values'][] = $value;
                }
            }
        }
        return $result;
    }
}
