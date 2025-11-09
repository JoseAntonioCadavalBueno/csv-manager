<?php

namespace CsvManager\Exceptions;
use CsvManager\Contracts\ICsvException;
use Exception;
use Throwable;

class NotFoundFileException extends Exception implements ICsvException
{
    public function __construct(?string $message = null, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}