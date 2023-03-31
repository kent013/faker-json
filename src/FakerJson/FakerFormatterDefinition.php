<?php declare(strict_types=1);

namespace FakerJson;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class FakerFormatterDefinition
{
    /**
     * default locale
     */
    public const DefaultLocale = 'default';

    /**
     * lexer
     * @var Lexer|null
     */
    protected static $lexer;

    /**
     * phpDocParser
     * @var PhpDocParser|null
     */
    protected static $phpDocParser;

    public function __construct(
        protected ReflectionMethod $method,
    ) {
    }

    /**
     * serialize into array
     * @return array<string,array<int,array<string,mixed>>|string>
     */
    public function toArray(): array
    {
        $classPath = $this->method->getDeclaringClass()->getName();
        $classPaths = explode('\\', $classPath);
        $locale = null;
        $providerName = null;

        if ($classPaths[1] === 'Core') {
            $providerName = 'Core';
        } elseif (count($classPaths) == 3) {
            $providerName = $classPaths[2];
        } else {
            $locale = $classPaths[2];
            $providerName = $classPaths[3];
        }
        $result = [
            'method' => $this->method->getName(),
            'provider' => $providerName,
        ];

        if (!is_null($locale) && preg_match('/^[a-z]{2}_[a-z]+(_[a-z]+)?$/i', $locale)) {
            $result['locale'] = $locale;
        } else {
            $result['locale'] = self::DefaultLocale;
        }

        $docCommentNode = $this->parseMethodDocComment();
        $parameterDocCommentNodes = [];

        if ($docCommentNode) {
            $parameterDocCommentNodes = $docCommentNode->getParamTagValues('@param');
        }

        $parameters = $this->method->getParameters();

        if (!empty($parameters)) {
            $result['parameters'] = [];

            foreach ($parameters as $parameter) {
                $parameterDocCommentNode = $this->filterParameterDocComment($parameterDocCommentNodes, $parameter->getName());
                $parameterDefinition = new FakerFormatterParameterDefinition($parameter, $parameterDocCommentNode);
                $result['parameters'][] = $parameterDefinition->toArray();
            }
        }

        if ($this->method->hasReturnType()) {
            $type = $this->method->getReturnType();
            Assert::notNull($type);
            Assert::isInstanceOf($type, ReflectionNamedType::class);
            $result['return_type'] = $type->getName();
        }
        return $result;
    }

    /**
     * @return array<int,mixed>
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public static function listDefinitions(): array
    {
        /** @var array<class-string> $classnames */
        $classnames = array_merge(
            ClassFinder::getClassesInNamespace('Faker\Core'),
            ClassFinder::getClassesInNamespace('Faker\Provider', ClassFinder::RECURSIVE_MODE)
        );
        return self::getFormatterDefinitionsFromClassnames($classnames);
    }

    /**
     * @param array<class-string> $classnames
     * @return array<int,mixed>
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public static function getFormatterDefinitionsFromClassnames(array $classnames): array
    {
        $definitions = [];

        foreach ($classnames as $classname) {
            $definitions = array_merge($definitions, self::getFormatterDefinitionsFromClassname($classname));
        }
        return array_values($definitions);
    }

    /**
     * @return array<int, mixed>
     * @param class-string $classname
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public static function getFormatterDefinitionsFromClassname(string $classname): array
    {
        $definitions = [];

        $class = new ReflectionClass($classname);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $formatterDefinition = new self($method);

            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }
            $methodName = $method->getName();

            if (isset($definitions[$methodName])) {
                continue;
            }

            if (in_array($methodName, ['__construct', 'withGenerator'])) {
                continue;
            }
            $definitions[$methodName] = $formatterDefinition->toArray();
        }
        return array_values($definitions);
    }

    /**
     * @return array<int,string>
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public static function listLocales(): array
    {
        $classnames = ClassFinder::getClassesInNamespace('Faker\Provider', ClassFinder::RECURSIVE_MODE);
        $locales = [];

        foreach ($classnames as $classname) {
            $classPaths = explode('\\', $classname);

            if (count($classPaths) != 4) {
                continue;
            }
            $locale = $classPaths[2];
            $locales[$locale] = $locale;
        }
        $locales = array_values($locales);
        sort($locales);
        return $locales;
    }

    /**
     * @param array<ParamTagValueNode> $parameterDocCommentNodes
     * @param string $name
     * @return ParamTagValueNode|null
     */
    protected function filterParameterDocComment(array $parameterDocCommentNodes, string $name): ParamTagValueNode|null
    {
        foreach ($parameterDocCommentNodes as $parameterDocCommentNode) {
            if ($parameterDocCommentNode->parameterName === "\${$name}") {
                return $parameterDocCommentNode;
            }
        }
        return null;
    }

    /**
     * phpstan lexer
     * @return Lexer
     */
    protected static function getLexer()
    {
        if (is_null(self::$lexer)) {
            self::$lexer = new Lexer();
        }
        return self::$lexer;
    }

    /**
     * get php doc parser
     * @return PhpDocParser
     */
    protected static function getPhpDocParser()
    {
        if (is_null(self::$phpDocParser)) {
            $constExprParser = new ConstExprParser();
            $typeParser = new TypeParser($constExprParser);
            self::$phpDocParser = new PhpDocParser($typeParser, $constExprParser);
        }
        return self::$phpDocParser;
    }

    /**
     * @param string $docComment
     * @return PhpDocNode
     */
    protected function parseDocComment(string $docComment)
    {
        $lexer = self::getLexer();
        $tokens = new TokenIterator($lexer->tokenize($docComment));
        $parser = self::getPhpDocParser();
        return $parser->parse($tokens);
    }

    /**
     * parse method docComment
     * @return PhpDocNode|null
     */
    protected function parseMethodDocComment(): PhpDocNode | null
    {
        $docComment = $this->method->getDocComment();

        if (!$docComment) {
            return null;
        }
        return $this->parseDocComment($docComment);
    }
}
