<?php

namespace CsvManager\Integrations;

use CsvManager\Contracts\ISource;
use CsvManager\Core\BaseCsv;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;

class NativeCsv extends BaseCsv
{
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
    public static function fromArray(
        array   $data,
        ISource $source,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\'
    ): string
    {
        self::validateCsvChars($delimiter, $enclosure, $escape);

        $source->validate(false);

        // Open to write the file.
        $file = fopen($source->getFullPath(), 'w');

        // Write the data on file.
        foreach ($data as $row) {
            fputcsv(
                $file,
                self::arrayFlattenAndNormalize($row),
                $delimiter,
                $enclosure,
                $escape
            );
        }

        fclose($file);
        return $source->getFullPath();
    }
}