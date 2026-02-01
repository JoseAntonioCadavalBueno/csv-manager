<?php

namespace CsvManager\Traits;

use InvalidArgumentException;
use CsvManager\Models\CsvFile;

trait Operators
{
    /**
     * Convert storage units.
     *
     * @param float|int $value
     * @param string $fromUnit
     * @param string $toUnit
     * @return float
     */
    protected static function convertStorageUnit(float|int $value, string $fromUnit, string $toUnit): float
    {
        $fromIndex  = CsvFile::ALLOW_UNITS[$fromUnit] ?? null;
        $toIndex    = CsvFile::ALLOW_UNITS[$toUnit] ?? null;

        if (is_null($fromIndex) || is_null($toIndex))
        {
            throw new InvalidArgumentException('Invalid units');
        }

        $value              = (float) $value;
        $unitDifference     = $fromIndex - $toIndex;
        $conversionFactor   = 1024 ** abs($unitDifference);

        return $unitDifference >= 0
            ? $value * $conversionFactor
            : $value / $conversionFactor;
    }

    /**
     * Detect and returns the enclosure of a csv.
     *
     * @param resource|string   $filePath
     * @param array             $listOfChars
     * @return string|false
     */
    protected static function detectedChar(
        mixed $filePath,
        array $listOfChars = []
    ): string|false
    {
        if (is_string($filePath))
        {
            $handle = @fopen($filePath, 'r');
            if ($handle === false)
            {
                return '';
            }
        } else
        {
            $handle = $filePath;
            rewind($handle);
        }

        $bestChar   = false;
        $maxCount   = 0;
        $lineCount  = 0;
        while (($line = @fgets($handle)) && $bestChar === false && $lineCount < 10)
        {
            foreach ($listOfChars as $char)
            {
                $count = substr_count($line, $char);
                if ($count > $maxCount)
                {
                    $maxCount  = $count;
                    $bestChar  = $char;
                }
            }
            $lineCount++;
        }

        if (is_string($filePath))
        {
            fclose($handle);
        }

        return $bestChar;
    }
}
