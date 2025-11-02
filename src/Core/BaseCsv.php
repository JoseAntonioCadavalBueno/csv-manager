<?php

namespace CsvManager\Core;

use CsvManager\Contracts\ISource;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Contracts\ICsv;
use InvalidArgumentException;
use LogicException;
use Throwable;

abstract class BaseCsv implements ICsv
{
    const MEMORY_LIMIT_PERCENT  = 0.8;
    const DEFAULT_MEMORY_LIMIT  = 134217728;

    const NOT_ALLOWED_CHARACTERS = ["\n", "\r"];

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
    abstract public static function fromArray(
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
    public static function toArray(
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
            throw new CorruptedFileException(previous: $exception);
        }

        $data = [];
        // Shared read lock.
        flock($file, LOCK_SH);

        // If is necessary have callable and is not included,
        // return a OverflowException because we cannot process the file.
        if (self::fileSizeExceedsMemoryLimit($source->getFullPath()) && is_null($function))
        {
            throw new OverflowException();
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
     * Boolean function that determines if the file size
     * is larger than the calculated percentage of php memory limit.
     *
     * @param string $filePath
     * @return bool
     */
    protected static function fileSizeExceedsMemoryLimit(string $filePath): bool
    {
        // The unit of measurement always in bytes.
        $fileSize = filesize($filePath);

        // If the file size is larger than the calculated percentage of php memory limit we return true.
        return $fileSize > self::calculateFreeMemory() * self::MEMORY_LIMIT_PERCENT;
    }

    /**
     * Function that calculates free memory at this point.
     *
     * @return int
     */
    protected static function calculateFreeMemory(): int
    {
        // The unit of measurement always in bytes.
        $memoryLimit = function_exists('ini_get')
            ? intval(ini_get('memory_limit')) * 1024 * 1024
            : self::DEFAULT_MEMORY_LIMIT;

        // Calculate the free memory, save it in the static variable and return it.
        $usedMemory = memory_get_usage(true);
        return $memoryLimit - $usedMemory;
    }

    /**
     * Check if the csv chars are valid.
     *
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return void
     */
    protected static function validateCsvChars(
        string $delimiter,
        string $enclosure,
        string $escape
    ): void
    {
        if (count(array_unique([$delimiter, $enclosure, $escape], SORT_REGULAR)) !== 3)
        {
            throw new LogicException(LanguageManager::getMessage('errors.same_csv_chars'));
        }

        $chars = [
            'delimiter' => $delimiter,
            'enclosure' => $enclosure,
            'escape'    => $escape
        ];
        foreach ($chars as $key => $char)
        {
            if (!self::isValidChar($char))
            {
                throw new InvalidArgumentException(
                    sprintf(LanguageManager::getMessage('errors.invalid_csv_Char'), $key)
                );
            }
        }
    }

    /**
     * Flat and normalize array data.
     *
     * @param array $array
     * @return array
     */
    protected static function arrayFlattenAndNormalize(array $array): array
    {
        $result = [];
        foreach ($array as $value)
        {
            if (is_array($value))
            {
                array_push($result, ...self::arrayFlattenAndNormalize($value));
            } else
            {
                $result[] = match ($value) {
                    is_bool($value) => $value ? 'true' : 'false',
                    default => trim(str_replace(self::NOT_ALLOWED_CHARACTERS, ' ', stripcslashes($value ?? ''))),
                };
            }
        }

        return $result;
    }

    /* ************************* */
    /* PRIVATE HELPERS FUNCTIONS */
    /* ************************* */

    /**
     * Check if is a valid char for csv.
     *
     * @param string $char
     * @return bool
     */
    private static function isValidChar(string $char): bool
    {
        // normalize single quotes to double quotes to always evaluate the byte itself
        // and avoid misinterpretation caused by using single quotes.
        $char = stripcslashes($char);
        return strlen($char) === 1 && ord($char) <= 127 && !in_array($char, self::NOT_ALLOWED_CHARACTERS);
    }
}