<?php

namespace CsvManager\Integrations;

use CsvManager\Core\BaseCsv;
use Illuminate\Support\Facades\Storage;

class LaravelCsv extends BaseCsv
{
    const PUBLIC_PATH   = 'app/public/';
    const STORAGE_PATH  = 'public';

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

        if (!is_null($customPath)) {
            $filePath   = self::sanitizeFileName($customPath);
            $publicPath = 'app' . DIRECTORY_SEPARATOR . $filePath . DIRECTORY_SEPARATOR . $filename;
        } else {
            $filePath   = self::STORAGE_PATH;
            $publicPath = self::PUBLIC_PATH . DIRECTORY_SEPARATOR . $filename;
        }

        $csvContent = '';
        foreach ($data as $row) {
            $csvContent .= $enclosure . implode($delimiter, $row) . $enclosure . "\n";
        }

        // Save the CSV in 'Storage/app/public/'
        Storage::disk($filePath)->put($filename, $csvContent);

        // Return the path of the generated file.
        return storage_path($publicPath);
    }
}