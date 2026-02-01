<?php

namespace CsvManager\Core;

use CsvManager\Models\CsvFile;
use CsvManager\Traits\Operators;

class MemoryInspector
{
    use Operators;
    const MEMORY_LIMIT_PERCENT  = 0.8;

    const MAP_STORAGE_UNITS = [
        'k' => CsvFile::KB,
        'm' => CsvFile::MB,
        'g' => CsvFile::GB
    ];

    /**
     * Boolean function that determines if the file size
     * is larger than the calculated percentage of php memory limit.
     *
     * @param string $filePath
     * @return bool
     */
    public static function fileSizeExceedsMemoryLimit(string $filePath): bool
    {
        // The unit of measurement always in bytes.
        $fileSize = filesize($filePath);

        // If the file size is larger than the calculated percentage of php memory limit we return true.
        return $fileSize > self::calculateFreeMemory() * self::MEMORY_LIMIT_PERCENT;
    }

    /* *************************** */
    /* PROTECTED HELPERS FUNCTIONS */
    /* *************************** */

    /**
     * Function that calculates free memory at this point.
     *
     * @return int
     */
    protected static function calculateFreeMemory(): int
    {
        $rawLimit = function_exists('ini_get') ? ini_get('memory_limit') : null;

        if ($rawLimit === false || is_null($rawLimit) || trim($rawLimit) === '-1')
        {
            return PHP_INT_MAX;
        }

        $memoryLimit = self::convertToBytes($rawLimit);
        // Calculate the free memory, save it in the static variable and return it.
        $usedMemory = memory_get_usage(true);
        return max(0, $memoryLimit - $usedMemory);
    }

    /**
     * Returns always in bytes.
     *
     * @param string $value
     * @return float
     */
    protected static function convertToBytes(string $value): float
    {
        $value = trim($value);
        $unit  = strtolower(substr($value, -1));
        $bytes = (float) $value;

        return self::convertStorageUnit($bytes, self::MAP_STORAGE_UNITS[$unit], CsvFile::B);
    }
}