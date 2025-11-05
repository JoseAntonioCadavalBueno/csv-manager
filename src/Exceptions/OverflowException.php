<?php

namespace CsvManager\Exceptions;
use CsvManager\Contracts\ICsvException;
use Exception;
use Throwable;

class OverflowException extends Exception implements ICsvException
{
    public function __construct(?string $message = null, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}