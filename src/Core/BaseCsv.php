<?php

namespace CsvManager\Core;

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
    const CSV_EXTENSION = 'csv';
    const TXT_EXTENSION = 'txt';
    const NOT_ALLOWED_CHARACTERS = ["\n", "\r"];

    const ALLOWED_EXTENSIONS = [ self::CSV_EXTENSION, self::TXT_EXTENSION ];
    const SANITIZE_REGEX = '/[^a-zA-Z0-9\/\\\\:\.\-_]/';

    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

    /**
     * A function that generates a CSV file from an array.
     *
     * @param array         $data
     * @param string|null   $filename
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string|null   $path
     * @param string|null   $disk
     * @return string
     * @throws CorruptedFileException|NotFoundFileException
     */
    abstract public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $path       = null,
        ?string $disk       = null
    ): string;

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
     * @throws OverflowException|NotFoundFileException|CorruptedFileException
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
        self::validateCsvChars($delimiter, $enclosure, $escape);

        // Sanitize the file path to prevent unexpected results.
        $filePath   = self::sanitizeFilePath($filePath);

        if (!self::isValidFile($filePath))
        {
            throw new NotFoundFileException(null);
        }

        try
        {
            $file = fopen($filePath, 'r');
        } catch (Throwable $exception)
        {
            // If the file cannot be opened, return a CorruptedFileException.
            throw new CorruptedFileException(previous: $exception);
        }

        $data = [];

        // Shared read lock.
        flock($file, LOCK_SH);

        while (($row = fgetcsv($file, $length, $delimiter, $enclosure, $escape)) !== false)
        {
            // If is necessary have callable and is not included,
            // return a OverflowException because we cannot process the file.
            if (self::fileSizeExceedsMemoryLimit($filePath) && is_null($function))
            {
                throw new OverflowException();
            }

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

        if (empty($data) && !is_null($function)) {
            return true;
        }
        return $data;
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
     * Function that sanitizes the $filePath variable to avoid unexpected results.
     *
     * @param string $filePath
     * @return string
     * @throws CorruptedFileException
     */
    protected static function sanitizeFilePath(string $filePath): string
    {
        $satinizedFilePath = preg_replace(self::SANITIZE_REGEX, '', $filePath);

        if ($filePath !== $satinizedFilePath)
        {
            throw new CorruptedFileException();
        }

        // Extract the extension's file.
        $extension = pathinfo($satinizedFilePath, PATHINFO_EXTENSION);
        $allowedExtensions = ConfigManager::get('allowed_extensions', self::ALLOWED_EXTENSIONS);
        $allowedExtensions = is_string($allowedExtensions)
            ? explode(',', $allowedExtensions)
            : $allowedExtensions;

        // If the extension's file is not allowed extension, throw a CorruptedFileException.
        if (empty($extension) || !in_array(strtolower($extension), $allowedExtensions))
        {
            throw new CorruptedFileException(LanguageManager::getMessage('errors.corrupt_2'));
        }

        return $satinizedFilePath;
    }

    /**
     * Function that sanitizes the $filename variable to avoid unexpected results.
     *
     * @param string $filename
     * @return string
     */
    protected static function sanitizeFileName(string $filename): string
    {
        return preg_replace(self::SANITIZE_REGEX, '', $filename);
    }

    /**
     * Function that generate a fileName.
     *
     * @param   string|null $filename
     * @return  string
     * @throws  CorruptedFileException
     */
    protected static function generateFileName(?string $filename = null): string
    {
        if (!is_null($filename))
        {
            // Extract the extension's file.
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (!empty($extension))
            {
                $allowedExtensions = ConfigManager::get('allowed_extensions', self::ALLOWED_EXTENSIONS);
                $allowedExtensions = is_string($allowedExtensions)
                    ? explode(',', $allowedExtensions)
                    : $allowedExtensions;

                if (!in_array($extension, $allowedExtensions))
                {
                    throw new CorruptedFileException(LanguageManager::getMessage('errors.corrupt_2'));
                }
                return $filename;
            }
            return $filename . '.' . self::CSV_EXTENSION;
        }
        return self::CSV_EXTENSION . '_' . uniqid() . '.' . self::CSV_EXTENSION;
    }

    /**
     * Check if exist and is valid file.
     *
     * @param string $satinizedFilePath
     * @return bool
     */
    protected static function isValidFile(string $satinizedFilePath): bool
    {
        return file_exists($satinizedFilePath) && is_readable($satinizedFilePath);
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

    /* *************************** */
    /* PRIVATE HELPERS FUNCTIONS */
    /* *************************** */

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