<?php

namespace CsvManager\Integrations;

use CsvManager\Contracts\ISource;
use CsvManager\Core\BaseCsv;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Sources\TrustedFylesystemSource;
use Illuminate\Support\Facades\Storage;
use JsonException;

final class LaravelCsv extends BaseCsv
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
    public function fromArray(
        array   $data,
        ISource $source,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\'
    ): string
    {
        $this->validateCsvChars($delimiter, $enclosure, $escape);

        $source->validate(false);

        return $this->handleData($data, $source, $delimiter, $enclosure, $escape);
    }

    /**
     * A function that generate a CSV file from a json.
     *
     * @param string    $data
     * @param ISource   $source
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @param bool      $associative
     * @param int       $depth
     * @param int       $flags
     * @return string
     * @throws CorruptedFileException
     * @throws NotFoundFileException
     * @throws JsonException
     */
    public function fromJson(
        string  $data,
        ISource $source,
        string  $delimiter      = ',',
        string  $enclosure      = '"',
        string  $escape         = '\\',
        bool    $associative    = false,
        int     $depth          = 512,
        int     $flags          = 0
    ): string
    {
        $this->validateCsvChars($delimiter, $enclosure, $escape);

        $source->validate(false);

        $data = json_decode($data, $associative, $depth, $flags|JSON_THROW_ON_ERROR);

        return $this->handleData($data, $source, $delimiter, $enclosure, $escape);
    }

    /* ******************************* */
    /* Private and protected functions */
    /* ******************************* */

    /**
     * Handle the array data and make a new csv file.
     *
     * @param array     $data
     * @param ISource   $source
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @return string
     */
    private function handleData(
        array   $data,
        ISource $source,
        string  $delimiter,
        string  $enclosure,
        string  $escape
    ): string
    {
        $disk = $source->getDisk() ?? self::STORAGE_PATH;
        $fullpath = Storage::disk($disk)->path($source->getFullPath());

        if ($this->config->get('legacy_mode'))
        {
            // Create a temporal stream on memory.
            $stream = fopen('php://temp', 'r+');

            $this->driver->from($data, $stream, $delimiter, $enclosure, $escape);

            rewind($stream);
            $csvContent = stream_get_contents($stream);

            Storage::disk($disk)->put($source->getFullPath(), $csvContent);
        } else
        {
            $this->driver->from(
                $data,
                new TrustedFylesystemSource($this->config, $this->language, $fullpath),
                $delimiter,
                $enclosure,
                $escape
            );
        }

        return $fullpath;
    }
}