<?php

namespace CsvManager\Integrations;

use CsvManager\Core\BaseCsv;
use Symfony\Component\Filesystem\Filesystem;

class SymfonyCsv extends BaseCsv
{
    const SYMFONY_DIR = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;

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
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $customPath = null
    ): string
    {
        $filename = self::generateFileName($filename);

        $filePath = !is_null($customPath)
            ? self::sanitizeFileName($customPath) . $filename
            : self::SYMFONY_DIR . $filename;

        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($filePath));

        $file = new SplFileObject($filePath, 'w');

        foreach ($data as $row) {
            $file->fputcsv($row, $delimiter, $enclosure);
        }

        return $filePath;
    }
}