<?php

namespace CsvManager\Integrations;

use CsvManager\Core\BaseCsv;
use CsvManager\Core\ConfigManager;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
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
     * @param string|null   $path
     * @param string|null   $disk
     * @return string
     * @throws CorruptedFileException|NotFoundFileException
     */
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $path       = null,
        ?string $disk       = null
    ): string
    {
        self::validateCsvChars($delimiter, $enclosure, '\\');
        $filename   = self::generateFileName($filename);
        $disk       = $disk ?? self::STORAGE_PATH;

        $relativePath = !is_null($path)
            ? self::sanitizeFilePath($path . DIRECTORY_SEPARATOR . $filename)
            : $filename;

        $dir = dirname(Storage::disk($disk)->path($relativePath));
        if (!is_dir($dir))
        {
            throw new NotFoundFileException(ConfigManager::get('errors.not_found_2'));
        }

        $csvContent = '';
        foreach ($data as $row)
        {
            $normalizedRow = self::arrayFlattenAndNormalize($row);
            $csvContent .= $enclosure . implode($delimiter, $normalizedRow) . $enclosure . "\n";
        }

        // Save the CSV
        Storage::disk($relativePath)->put($filename, $csvContent);

        // Return the path of the generated file.
        return Storage::disk($disk)->path($relativePath);
    }
}