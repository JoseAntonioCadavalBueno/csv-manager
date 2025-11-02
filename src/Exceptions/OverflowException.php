<?php

namespace CsvManager\Exceptions;
use CsvManager\Contracts\ICsvException;
use Exception;
use CsvManager\Core\LanguageManager;
use Throwable;

class OverflowException extends Exception implements ICsvException
{
    public function __construct(?string $message = null, int $code = 500, ?Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = LanguageManager::getMessage('errors.overflow');
        }

        parent::__construct($message, $code, $previous);
    }
}