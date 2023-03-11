<?php declare(strict_types=1);

namespace FakerJson;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class FakerFormatterDefinition
{
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

        if (!is_null($locale)) {
            $result['locale'] = $locale;
        }
        $parameters = $this->method->getParameters();

        if (!empty($parameters)) {
            $result['parameters'] = [];

            foreach ($parameters as $parameter) {
                $parameterDefinition = new FakerFormatterParameterDefinition($parameter);
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
            if (in_array($classname, ['Base'])) {
                continue;
            }
            $class = new ReflectionClass($classname);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $formatterDefinition = new self($method);
                $methodName = $method->getName();

                if (isset($definitions[$methodName])) {
                    continue;
                }

                if (in_array($methodName, ['__construct', 'withGenerator'])) {
                    continue;
                }
                $definitions[$methodName] = $formatterDefinition->toArray();
            }
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
}
