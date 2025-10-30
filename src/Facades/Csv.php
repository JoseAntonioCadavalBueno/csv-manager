<?php

namespace CsvManager\Facades;

use CsvManager\Contracts\ICsv;
use CsvManager\Contracts\ISource;
use CsvManager\Core\ConfigManager;
use CsvManager\Core\LanguageManager;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Integrations\LaravelCsv;
use CsvManager\Integrations\NativeCsv;
use CsvManager\Integrations\SymfonyCsv;
use CsvManager\Sources\StdinSource;
use CsvManager\Sources\TrustedFylesystemSource;
use CsvManager\Sources\UntrustedSource;
use LogicException;

class Csv
{
    const LARAVEL_ENV = 'laravel';
    const SYMFONY_ENV = 'symfony';
    const NATIVE_ENV  = 'native';
    const ALLOWED_ENV_CONFIG = [self::NATIVE_ENV, self::LARAVEL_ENV, self::SYMFONY_ENV];

    const UNTRUSTED_PATH_REGEX = '/\.\.|[<>:"|?*]/';

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
            self::resolveSource($filePath),
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
     * @param string        $escape
     * @param string|null   $customPath
     * @param string|null   $disk
     * @return string
     * @throws CorruptedFileException|NotFoundFileException
     */
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\',
        ?string $customPath = null,
        ?string $disk       = null
    ): string
    {
        self::resolveInstance();

        if (!is_null($customPath))
        {
            $filePath = $customPath;
        } else
        {
            $filePath = $filename;
            $filename = null;
        }
        return self::$instance::fromArray(
            $data,
            self::resolveSource($filePath, $filename, $disk),
            $delimiter,
            $enclosure,
            $escape
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
            $env = ConfigManager::get('env_config') ?? self::NATIVE_ENV;

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

    /**
     * Detects and builds the correct source type based on environment and input.
     *
     * @param string        $filePath
     * @param string|null   $filename
     * @param string|null   $disk
     * @return ISource
     */
    private static function resolveSource(string $filePath, ?string $filename = null, ?string $disk = null): ISource
    {
        if ($filePath === StdinSource::DEFAULT_STDIN_PATH)
        {
            return new StdinSource($filename, $disk);
        }

        if (preg_match(self::UNTRUSTED_PATH_REGEX, $filePath))
        {
            return new UntrustedSource($filePath, $filename, $disk);
        }

        return new TrustedFylesystemSource($filePath, $filename, $disk);
    }
}
