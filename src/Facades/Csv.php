<?php

namespace CsvManager\Facades;

use CsvManager\Contracts\ICsv;
use CsvManager\Contracts\ISource;
use CsvManager\Core\Config;
use CsvManager\Core\Language;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Integrations\LaravelCsv;
use CsvManager\Integrations\NativeCsv;
use CsvManager\Integrations\SymfonyCsv;
use CsvManager\Sources\StdinSource;
use CsvManager\Sources\TrustedFylesystemSource;
use CsvManager\Sources\UntrustedSource;
use JsonException;
use src\Exceptions\InvalidConfigurationException;

class Csv
{
    const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    const DEFAULT_CONFIG_PATH   = self::BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'csv-manager.php';
    const CUSTOM_CONFIG_PATH    = self::BASE_PATH . '..'
        . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . 'config'
        . DIRECTORY_SEPARATOR . 'csv-manager.php';

    const LARAVEL_ENV = 'laravel';
    const SYMFONY_ENV = 'symfony';
    const NATIVE_ENV  = 'native';
    const ALLOWED_ENV_CONFIG = [self::NATIVE_ENV, self::LARAVEL_ENV, self::SYMFONY_ENV];

    const UNTRUSTED_PATH_REGEX = '/\.\.|[<>:"|?*]/';

    private static ICsv $instance;
    private static Config $config;
    private static Language $language;

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
     * @throws CorruptedFileException|NotFoundFileException|OverflowException|InvalidConfigurationException
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

        return self::$instance->toArray(
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
     * A function that processes a csv file and converts it into a json.
     *
     * @param string        $filePath
     * @param bool          $header
     * @param callable|null $function
     * @param int|null      $length
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @param int           $flags
     * @return string|bool
     *
     * @throws CorruptedFileException
     * @throws JsonException
     * @throws NotFoundFileException
     * @throws OverflowException
     * @throws InvalidConfigurationException
     */
    public static function toJson(
        string      $filePath,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\',
        int         $flags      = 0
    ): string|bool
    {
        self::resolveInstance();

        return self::$instance->toJson(
            self::resolveSource($filePath),
            $header,
            $function,
            $length,
            $delimiter,
            $enclosure,
            $escape,
            $flags
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
     * @throws CorruptedFileException|NotFoundFileException|InvalidConfigurationException
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
        return self::$instance->fromArray(
            $data,
            self::resolveSource($filePath, $filename, $disk),
            $delimiter,
            $enclosure,
            $escape
        );
    }

    /**
     * A function that generate a CSV file from a json.
     *
     * @param string        $data
     * @param string|null   $filename
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @param bool          $associative
     * @param int           $depth
     * @param int           $flags
     * @param string|null   $customPath
     * @param string|null   $disk
     * @return string
     * @throws InvalidConfigurationException
     */
    public static function fromJson(
        string  $data,
        ?string $filename       = null,
        string  $delimiter      = ',',
        string  $enclosure      = '"',
        string  $escape         = '\\',
        bool    $associative    = false,
        int     $depth          = 512,
        int     $flags          = 0,
        ?string $customPath     = null,
        ?string $disk           = null
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

        return self::$instance->fromJson(
            $data,
            self::resolveSource($filePath, $filename, $disk),
            $delimiter,
            $enclosure,
            $escape,
            $associative,
            $depth,
            $flags
        );
    }

    /* ************************* */
    /* PRIVATE HELPERS FUNCTIONS */
    /* ************************* */

    /**
     * Configure the correct integration for ICsv.
     *
     * @return void
     * @throws InvalidConfigurationException
     */
    private static function resolveInstance(): void
    {
        if (!isset(self::$instance))
        {
            self::resolveConfig();
            $env = self::$config->get('env_config') ?? self::NATIVE_ENV;

            if (!in_array($env, self::ALLOWED_ENV_CONFIG))
            {
                throw new InvalidConfigurationException(self::$language->getMessage('errors.illegal_env'));
            }

            if ($env === self::LARAVEL_ENV && class_exists('Illuminate\Support\Facades\Storage'))
            {
                self::$instance = new LaravelCsv(self::$language);
            } elseif ($env === self::SYMFONY_ENV && class_exists('Symfony\Component\Filesystem\Filesystem'))
            {
                @trigger_error(
                    'Support for the Symfony environment is deprecated; use the Laravel or Native environment instead.',
                    E_USER_DEPRECATED
                );
                self::$instance = new SymfonyCsv(self::$language);
            } else
            {
                self::$instance = new NativeCsv(self::$language);
            }
        }

        if (!isset(self::$instance))
        {
            throw new InvalidConfigurationException(self::$language->getMessage('errors.illegal_env'));
        }
    }

    /**
     * Configure the correct language and config for this facade.
     *
     * @return void
     */
    private static function resolveConfig(): void
    {
        $config = file_exists(self::CUSTOM_CONFIG_PATH)
            ? require self::CUSTOM_CONFIG_PATH
            : require self::DEFAULT_CONFIG_PATH;

        self::$config   = new Config($config);
        self::$language = new Language(self::$config);
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
            return new StdinSource(self::$language, $filename, $disk);
        }

        if (preg_match(self::UNTRUSTED_PATH_REGEX, $filePath))
        {
            return new UntrustedSource(self::$config, self::$language, $filePath, $filename, $disk);
        }

        return new TrustedFylesystemSource(self::$config, self::$language, $filePath, $filename, $disk);
    }
}
