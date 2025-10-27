<?php

namespace CsvManager\Integrations;

use CsvManager\Core\ConfigManager;
use LogicException;
use CsvManager\Core\BaseCsv;
use CsvManager\Core\LanguageManager;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;

class NativeCsv extends BaseCsv
{
    /**
     * A function that generates a CSV file from an array.
     *
     * @param array         $data
     * @param string|null   $filename
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @param string|null   $path
     * @param string|null   $disk
     * @return string
     * @throws CorruptedFileException
     * @throws NotFoundFileException
     */
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\',
        ?string $path       = null,
        ?string $disk       = null
    ): string
    {
        self::validateCsvChars($delimiter, $enclosure, $escape);
        // In native php projects $filename must be the fullPath.
        if (is_null($filename)) {
            throw new NotFoundFileException();
        }

        // In native php projects $customPath always be null.
        if (!is_null($path) || !is_null($disk)) {
            throw new LogicException(LanguageManager::getMessage('errors.native_logic'));
        }

        // Must sanitize the value of filename.
        $filename = self::sanitizeFilePath($filename);

        // Open to write the file.
        $file = fopen($filename, 'w');

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
        return $filename;
    }
}