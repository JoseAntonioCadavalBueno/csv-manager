<?php

namespace CsvManager\src\Exceptions;
use Exception;
use CsvManager\src\Core\LanguageManager;
use Throwable;

class CorruptedFileException extends Exception
{
    public function __construct(?string $message = null, int $code = 415, ?Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = LanguageManager::getMessage('errors.corrupt');
        }

        parent::__construct($message, $code, $previous);
    }
}