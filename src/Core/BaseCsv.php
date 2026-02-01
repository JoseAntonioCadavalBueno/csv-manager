<?php

namespace CsvManager\Core;

use CsvManager\Contracts\IDriver;
use CsvManager\Contracts\ISource;
use CsvManager\Drivers\LegacyDriver;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Contracts\ICsv;
use CsvManager\Facades\Csv;
use CsvManager\Traits\CsvValidator;
use InvalidArgumentException;
use JsonException;
use LogicException;
use src\Drivers\StandardDriver;

abstract class BaseCsv implements ICsv
{
    use CsvValidator;

    const NOT_ALLOWED_CHARACTERS = ["\n", "\r"];

    protected Language  $language;
    protected Config    $config;
    protected IDriver   $driver;
    protected MemoryInspector $memoryInspector;
    public function __construct(Language $language, ?Config $config = null)
    {
        $this->language         = $language;
        $this->memoryInspector  = self::instanceMemoryInspector();

        if (is_null($config))
        {
            $configData = file_exists(Csv::CUSTOM_CONFIG_PATH)
                ? require Csv::CUSTOM_CONFIG_PATH
                : require Csv::DEFAULT_CONFIG_PATH;

            $this->config = new Config($configData);
        } else
        {
            $this->config = $config;
        }

        if ($this->config->get('legacy_mode'))
        {
            $this->driver = new LegacyDriver($this->language);
        } else
        {
            $this->driver = new StandardDriver();
        }
    }

    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

    /**
     * A function that generates a CSV file from an array.
     *
     * @param array     $data
     * @param ISource   $source
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @return string
     * @throws CorruptedFileException
     * @throws NotFoundFileException
     */
    abstract public function fromArray(
        array   $data,
        ISource $source,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\'
    ): string;

    /**
     * A function that generate a CSV file from a json.
     *
     * @param string    $data
     * @param ISource   $source
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @param bool      $associative
     * @param int       $depth
     * @param int       $flags
     * @return string
     * @throws CorruptedFileException
     * @throws NotFoundFileException
     * @throws JsonException
     */
    abstract public function fromJson(
        string  $data,
        ISource $source,
        string  $delimiter      = ',',
        string  $enclosure      = '"',
        string  $escape         = '\\',
        bool    $associative    = false,
        int     $depth          = 512,
        int     $flags          = 0
    ): string;

    /**
     * A function that processes a csv file and converts it into an array.
     *
     * @param ISource       $source
     * @param bool          $header
     * @param callable|null $function
     * @param int|null      $length
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @return array|bool
     * @throws OverflowException|NotFoundFileException|CorruptedFileException
     */
    public function toArray(
        ISource     $source,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\'
    ): array|bool
    {
        self::validateCsvChars($delimiter, $enclosure, $escape);

        $source->validate();

        // If is necessary have callable and is not included,
        // return a OverflowException because we cannot process the file.
        if ($this->memoryInspector->fileSizeExceedsMemoryLimit($source->getFullPath()) && is_null($function))
        {
            throw new OverflowException($this->language->getMessage('errors.overflow'));
        }

        return $this->driver->to($source, $header, $function, $length, $delimiter, $enclosure, $escape);
    }

    /**
     * A function that processes a csv file and converts it into a json.
     *
     * @param ISource       $source
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
     */
    public function toJson(
        ISource     $source,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\',
        int         $flags      = JSON_PRETTY_PRINT
    ): string|bool
    {
        self::validateCsvChars($delimiter, $enclosure, $escape);

        $source->validate();

        // If is necessary have callable and is not included,
        // return a OverflowException because we cannot process the file.
        if ($this->memoryInspector->fileSizeExceedsMemoryLimit($source->getFullPath()) && is_null($function))
        {
            throw new OverflowException($this->language->getMessage('errors.overflow'));
        }

        return json_encode(
            $this->driver->to($source, $header, $function, $length, $delimiter, $enclosure, $escape),
            $flags|JSON_THROW_ON_ERROR
        );
    }

    /* *************************** */
    /* PROTECTED HELPERS FUNCTIONS */
    /* *************************** */

    /**
     * Check if the csv chars are valid.
     *
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return void
     */
    protected function validateCsvChars(
        string $delimiter,
        string $enclosure,
        string $escape
    ): void
    {
        if (count(array_unique([$delimiter, $enclosure, $escape], SORT_REGULAR)) !== 3)
        {
            throw new LogicException($this->language->getMessage('errors.same_csv_chars'));
        }

        $chars = [
            'delimiter' => $delimiter,
            'enclosure' => $enclosure,
            'escape'    => $escape
        ];
        foreach ($chars as $key => $char)
        {
            if (!self::isValidChar($char, self::NOT_ALLOWED_CHARACTERS))
            {
                throw new InvalidArgumentException(
                    sprintf($this->language->getMessage('errors.invalid_csv_Char'), $key)
                );
            }
        }
    }

    /* ************************ */
    /* PRIVATE HELPER FUNCTIONS */
    /* ************************ */

    /**
     * Instance MemoryInspector.
     *
     * @return MemoryInspector
     */
    private static function instanceMemoryInspector(): MemoryInspector
    {
        return new MemoryInspector();
    }
}