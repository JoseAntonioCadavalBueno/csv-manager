<?php

namespace CsvManager\Facades;

use CsvManager\Contracts\ICsv;
use CsvManager\Core\ConfigManager;
use CsvManager\Core\LanguageManager;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Integrations\LaravelCsv;
use CsvManager\Integrations\NativeCsv;
use CsvManager\Integrations\SymfonyCsv;
use LogicException;

class Csv
{
    const LARAVEL_ENV = 'laravel';
    const SYMFONY_ENV = 'symfony';
    const NATIVE_ENV  = 'native';
    const ALLOWED_ENV_CONFIG = [self::NATIVE_ENV, self::LARAVEL_ENV, self::SYMFONY_ENV];

    /** @var ICsv $instance */
    private static ICsv $instance;

    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

    /**
     * A function that processes a csv file and converts it into an array.
     *
     * @param string        $filePath
     * @param bool          $header
     * @param callable|null $function
     * @param int|null      $length
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @return array|bool
     * @throws CorruptedFileException|NotFoundFileException|OverflowException
     */
    public static function toArray(
        string      $filePath,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\'
    ): array|bool
    {
        self::resolveInstance();

        return self::$instance::toArray(
            $filePath,
            $header,
            $function,
            $length,
            $delimiter,
            $enclosure,
            $escape
        );
    }

    /**
     * A function that generates a CSV file from an array.
     *
     * @param array         $data
     * @param string|null   $filename
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string|null   $customPath
     * @return string
     */
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $customPath = null
    ): string
    {
        self::resolveInstance();

        return self::$instance::fromArray(
            $data,
            $filename,
            $delimiter,
            $enclosure,
            $customPath
        );
    }

    /* ************************* */
    /* PRIVATE HELPERS FUNCTIONS */
    /* ************************* */

    /**
     * Configure the correct integration for ICsv.
     *
     * @return void
     */
    private static function resolveInstance(): void
    {
        if (!isset(self::$instance)) {
            $env = ConfigManager::get('env_config');

            if (!in_array($env, self::ALLOWED_ENV_CONFIG))
            {
                throw new LogicException(LanguageManager::getMessage('errors.illegal_env'));
            }

            if ($env === self::LARAVEL_ENV && class_exists('Illuminate\Support\Facades\Storage')) {
                self::$instance = new LaravelCsv();
            } elseif ($env === self::SYMFONY_ENV && class_exists('Symfony\Component\Filesystem\Filesystem')) {
                self::$instance = new SymfonyCsv();
            } else {
                self::$instance = new NativeCsv();
            }
        }
    }
}
