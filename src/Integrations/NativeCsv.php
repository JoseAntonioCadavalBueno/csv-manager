<?php

namespace CsvManager\Integrations;

use CsvManager\Contracts\ISource;
use CsvManager\Core\BaseCsv;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use JsonException;

final class NativeCsv extends BaseCsv
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

        if ($this->config->get('legacy_mode'))
        {
            // Open to write the file.
            $file = fopen($source->getFullPath(), 'w');

            $this->driver->from($data, $file, $delimiter, $enclosure, $escape);

            fclose($file);
        } else
        {
            $this->driver->from($data, $source, $delimiter, $enclosure, $escape);
        }

        return $source->getFullPath();
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

        $this->handleData($data, $source, $delimiter, $enclosure, $escape);

        return $source->getFullPath();
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
     * @return void
     */
    private function handleData(
        array   $data,
        ISource $source,
        string  $delimiter,
        string  $enclosure,
        string  $escape
    ): void
    {
        if ($this->config->get('legacy_mode'))
        {
            // Open to write the file.
            $file = fopen($source->getFullPath(), 'w');

            $this->driver->from($data, $file, $delimiter, $enclosure, $escape);

            fclose($file);
        } else
        {
            $this->driver->from($data, $source, $delimiter, $enclosure, $escape);
        }
    }
}