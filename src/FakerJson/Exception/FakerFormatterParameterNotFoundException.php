<?php declare(strict_types=1);

namespace FakerJson\Exception;

use Exception;
use Throwable;

class FakerFormatterParameterNotFoundException extends Exception
{
    protected string $methodName;
    protected string $parameterName;

    public function __construct(string $methodName, string $parameterName, Throwable|null $previous = null, string $message = null)
    {
        if (is_null($message)) {
            $message = "parameter {$parameterName} is not found in {$methodName} formatter definition.";
        }
        parent::__construct($message, 0, $previous);
        $this->methodName = $methodName;
        $this->parameterName = $parameterName;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function parameterName(): string
    {
        return $this->parameterName;
    }
}
