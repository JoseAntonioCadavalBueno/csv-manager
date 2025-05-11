<?php

namespace src\Exceptions;
use Exception;
use src\Core\LanguageManager;
use Throwable;

class OverflowException extends Exception
{
    public function __construct(?string $message = null, int $code = 500, ?Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = LanguageManager::getMessage('errors.overflow');
        }

        parent::__construct($message, $code, $previous);
    }
}