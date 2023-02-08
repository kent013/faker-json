<?php declare(strict_types=1);

namespace FakerJson\Exception;

use Exception;
use Throwable;

class FakerFormatterNotFoundException extends Exception
{
    protected string $methodName;

    public function __construct(string $methodName, Throwable|null $previous = null, string|null $message = null)
    {
        if (is_null($message)) {
            $message = "formatter {$methodName} is not found";
        }
        parent::__construct($message, 0, $previous);
        $this->methodName = $methodName;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }
}
