<?php declare(strict_types=1);

namespace FakerJson;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Webmozart\Assert\Assert;

class FakerFormatterParameterDefinition
{
    /**
     * @param ReflectionParameter $parameter
     * @param ParamTagValueNode|null $parameterDocCommentNode
     */
    public function __construct(
        protected ReflectionParameter $parameter,
        protected ParamTagValueNode|null $parameterDocCommentNode,
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
        } elseif ($this->parameterDocCommentNode) {
            $result['has_type'] = true;
            $result['type'] = FakerFormatterDefinition::convertTypeNodeToString($this->parameterDocCommentNode->type);
        }
        return $result;
    }
}
