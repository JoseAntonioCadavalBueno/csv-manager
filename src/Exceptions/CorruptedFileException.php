<?php

namespace CsvManager\Exceptions;
use Exception;
use CsvManager\Core\LanguageManager;
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