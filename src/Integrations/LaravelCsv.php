<?php

namespace CsvManager\Integrations;

use CsvManager\Contracts\ISource;
use CsvManager\Core\BaseCsv;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use Illuminate\Support\Facades\Storage;

class LaravelCsv extends BaseCsv
{
    const STORAGE_PATH  = 'public';

    /**
     * A function that generates a CSV file from an array.
     *
     * @param array     $data
     * @param ISource   $source
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @return string
     * @throws CorruptedFileException|NotFoundFileException
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

        // Create a temporal stream on memory.
        $stream = fopen('php://temp', 'r+');

        foreach ($data as $row)
        {
            fputcsv(
                $stream,
                self::arrayFlattenAndNormalize($row),
                $delimiter,
                $enclosure,
                $escape
            );
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        $disk = $source->getDisk() ?? self::STORAGE_PATH;

        Storage::disk($disk)->put($source->getFullPath(), $csvContent);
        return Storage::disk($disk)->path($source->getFullPath());
    }
}