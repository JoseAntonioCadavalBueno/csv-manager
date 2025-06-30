<?php

namespace CsvManager\Core;

use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Contracts\ICsv;
use Throwable;

abstract class BaseCsv implements ICsv
{
    const MEMORY_LIMIT_PERCENT  = 0.8;
    const DEFAULT_CHUNK_SIZE    = 50000;
    const DEFAULT_MEMORY_LIMIT  = 134217728;
    const CSV_EXTENSION = 'csv';
    const SANITIZE_REGEX = '/[^a-zA-Z0-9\/\.\-_]/';

    /** @var int $freeMemory */
    protected static int $freeMemory;

    /* **************** */
    /* GETTER FUNCTIONS */
    /* **************** */

    /**
     * A getter function that returns the value of $freeMemory.
     *
     * @return int
     */
    public static function getFreeMemory(): int
    {
        return self::$freeMemory;
    }

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
     * @param string|null   $customPath
     * @return string
     */
    abstract public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $customPath = null
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
        // Sanitize the file path to prevent unexpected results.
        $filePath   = self::sanitizeFilePath($filePath);
        try
        {
            $file = fopen($filePath, 'r');
        } catch (Throwable $exception)
        {
            // If the file cannot be opened, return a NotFoundFileException.
            throw new NotFoundFileException(null, 404, $exception);
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

        if (empty($data)) {
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
        // Calculate the free memory before processing.
        self::calculateFreeMemory();

        // The unit of measurement always in bytes.
        $fileSize = filesize($filePath);

        // If the file size is larger than the calculated percentage of php memory limit we return true.
        return $fileSize > self::$freeMemory * self::MEMORY_LIMIT_PERCENT;
    }

    /**
     * Function that determines the best size
     * of the chunk depending on the available free memory.
     *
     * @param int|null $chunkSize
     * @return int
     */
    protected static function calculateChunkSize(?int $chunkSize): int
    {
        if (is_null($chunkSize)) {
            $chunkSize = self::DEFAULT_CHUNK_SIZE;
        }

        return min(5000, intval(self::$freeMemory / $chunkSize));
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
        self::$freeMemory = $memoryLimit - $usedMemory;

        return self::$freeMemory;
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

        // If the extension's file is different like csv, throw a CorruptedFileException.
        if (!empty($extension) && strtolower($extension) !== self::CSV_EXTENSION)
        {
            throw new CorruptedFileException(LanguageManager::getMessage('errors.corrupt_2'));
        }

        if (file_exists($satinizedFilePath) && !is_readable($satinizedFilePath))
        {
            throw new CorruptedFileException();
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
     * @param string|null $filename
     * @return string
     */
    protected static function generateFileName(?string $filename = null): string
    {
        return !is_null($filename)
            ? self::sanitizeFileName($filename). '.' . self::CSV_EXTENSION
            : self::CSV_EXTENSION . '_' . uniqid() . '.' . self::CSV_EXTENSION;
    }
}