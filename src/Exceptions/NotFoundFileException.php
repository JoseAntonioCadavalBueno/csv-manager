<?php

namespace src\Exceptions;
use Exception;
use src\Core\LanguageManager;
use Throwable;

class NotFoundFileException extends Exception
{
    public function __construct(?string $message = null, int $code = 404, ?Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = LanguageManager::getMessage('errors.not_found');
        }

        parent::__construct($message, $code, $previous);
    }
}