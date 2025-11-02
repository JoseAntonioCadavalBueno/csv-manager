<?php

namespace CsvManager\Exceptions;
use CsvManager\Contracts\ICsvException;
use Exception;
use CsvManager\Core\LanguageManager;
use Throwable;

class CorruptedFileException extends Exception implements ICsvException
{
    public function __construct(?string $message = null, int $code = 415, ?Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = LanguageManager::getMessage('errors.corrupt');
        }

        parent::__construct($message, $code, $previous);
    }
}