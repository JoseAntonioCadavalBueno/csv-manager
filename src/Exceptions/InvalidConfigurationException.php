<?php

namespace src\Exceptions;

use CsvManager\Contracts\ICsvException;
use CsvManager\Core\LanguageManager;
use Exception;
use Throwable;

class InvalidConfigurationException extends Exception implements ICsvException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        if (is_null($message))
        {
            $message = LanguageManager::getMessage('errors.illegal_env');
        }

        parent::__construct($message, $code, $previous);
    }
}