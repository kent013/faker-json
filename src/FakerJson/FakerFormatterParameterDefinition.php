<?php declare(strict_types=1);

namespace FakerJson;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use ReflectionNamedType;
use ReflectionParameter;
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
            $result['default_value'] = $this->parameter->getDefaultValue();
        }

        if ($this->parameter->hasType()) {
            $type = $this->parameter->getType();
            Assert::notNull($type);
            Assert::isInstanceOf($type, ReflectionNamedType::class);
            $result['has_type'] = true;
            $result['type'] = $type->getName();
        } elseif ($this->parameterDocCommentNode) {
            $result['has_type'] = true;

            if ($this->parameterDocCommentNode->type instanceof UnionTypeNode) {
                $result['type'] = implode(',', $this->convertTypes($this->parameterDocCommentNode->type->types));
            } elseif ($this->parameterDocCommentNode->type instanceof IdentifierTypeNode) {
                $result['type'] = $this->convertType($this->parameterDocCommentNode->type->name);
            } else {
                $result['type'] = (string) $this->parameterDocCommentNode->type;
            }
        }
        return $result;
    }

    /**
     * convert type and remove null/duplicated types
     * @param array<IdentifierTypeNode> $types
     * @return array<string>
     */
    protected function convertTypes(array $types): array
    {
        $converted = [];

        foreach ($types as $type) {
            if ($type === 'null') {
                continue;
            }
            $converted[] = $this->convertType($type->name);
        }
        return array_unique($converted);
    }

    /**
     * convert type
     * @param string $type
     * @return string
     */
    protected function convertType(string $type): string
    {
        return match ($type) {
            '\DateTime' => 'datetime',
            'int', 'float' => 'number',
            'string' => 'string',
            'bool' => 'boolean',
            'array' => 'array',
            'null' => 'null',
            default => $type
        };
    }
}
