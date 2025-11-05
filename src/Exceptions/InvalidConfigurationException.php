<?php

namespace src\Exceptions;

use CsvManager\Contracts\ICsvException;
use Exception;
use Throwable;

class InvalidConfigurationException extends Exception implements ICsvException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}