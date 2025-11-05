<?php

namespace CsvManager\Core;

use CsvManager\Contracts\ISource;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Contracts\ICsv;
use CsvManager\Traits\CsvValidator;
use InvalidArgumentException;
use LogicException;
use Throwable;

abstract class BaseCsv implements ICsv
{
    use CsvValidator;

    const NOT_ALLOWED_CHARACTERS = ["\n", "\r"];

    protected LanguageManager $language;
    protected MemoryInspector $memoryInspector;
    public function __construct(LanguageManager $language)
    {
        $this->language         = $language;
        $this->memoryInspector  = self::instanceMemoryInspector();
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

        try
        {
            $file = fopen($source->getFullPath(), 'r');
        } catch (Throwable $exception)
        {
            // If the file cannot be opened, return a CorruptedFileException.
            throw new CorruptedFileException(message: $this->language->getMessage('errors.corrupt'), previous: $exception);
        }

        $data = [];
        // Shared read lock.
        flock($file, LOCK_SH);

        // If is necessary have callable and is not included,
        // return a OverflowException because we cannot process the file.
        if ($this->memoryInspector->fileSizeExceedsMemoryLimit($source->getFullPath()) && is_null($function))
        {
            throw new OverflowException($this->language->getMessage('errors.overflow'));
        }

        while (($row = fgetcsv($file, $length, $delimiter, $enclosure, $escape)) !== false)
        {
            // If header is true, jump to the next row.
            if ($header)
            {
                $header = false;
                continue;
            }

            if (!is_null($function))
            {
                $function($row);
            } else
            {
                $data[] = $row;
            }
        }

        // Release shared read lock and close the file.
        flock($file, LOCK_UN);
        fclose($file);

        return empty($data) && !is_null($function) ? true : $data;
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