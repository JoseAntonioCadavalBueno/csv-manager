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
     * @param string|null   $customPath
     * @return string
     * @throws CorruptedFileException|NotFoundFileException|LogicException
     */
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $customPath = null
    ): string
    {
        // In native php projects $filename must be the fullPath.
        if (is_null($filename)) {
            throw new NotFoundFileException();
        }

        // In native php projects $customPath always be null.
        if (!is_null($customPath)) {
            throw new LogicException(LanguageManager::getMessage('errors.logic'));
        }

        // Must sanitize the value of filename.
        $filename = self::sanitizeFilePath($filename);

        // Open to write the file.
        $file = fopen($filename, 'w');

        // Write the data on file.
        foreach ($data as $row) {
            fputcsv($file, $row, $delimiter, $enclosure);
        }

        fclose($file);
        return $filename;
    }
}