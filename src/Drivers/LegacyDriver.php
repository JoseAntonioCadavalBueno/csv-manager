<?php

namespace CsvManager\Drivers;

use CsvManager\Contracts\IDriver;
use CsvManager\Contracts\ISource;
use CsvManager\Core\BaseCsv;
use CsvManager\Core\Language;
use CsvManager\Exceptions\CorruptedFileException;
use SplFileObject;
use Throwable;

class LegacyDriver implements IDriver
{
    private Language $language;
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * A function that generate a Csv file from an array.
     *
     * @param array                             $data
     * @param resource|SplFileObject|ISource    $source
     * @param string                            $delimiter
     * @param string                            $enclosure
     * @param string                            $escape
     * @return void
     */
    public function from(
        array   $data,
        mixed   $source,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\'
    ): void
    {

        // Write the data on file.
        foreach ($data as $row) {
            if ($source instanceof SplFileObject)
            {
                $source->fputcsv(
                    BaseCsv::arrayFlattenAndNormalize($row, BaseCsv::NOT_ALLOWED_CHARACTERS),
                    $delimiter,
                    $enclosure,
                    $escape
                );
            } else
            {
                fputcsv(
                    $source,
                    BaseCsv::arrayFlattenAndNormalize($row, BaseCsv::NOT_ALLOWED_CHARACTERS),
                    $delimiter,
                    $enclosure,
                    $escape
                );
            }
        }
    }

    /**
     * A function that processes a csv file and converts it into an array.
     *
     * @param ISource       $source
     * @param bool          $header
     * @param callable|null $function
     * @param int|null      $length
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @return array|bool
     * @throws CorruptedFileException
     */
    public function to(
        ISource     $source,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\'
    ): array|bool
    {
        $data = [];

        try
        {
            $file = fopen($source->getFullPath(), 'r');
        } catch (Throwable $exception)
        {
            // If the file cannot be opened, return a CorruptedFileException.
            throw new CorruptedFileException(message: $this->language->getMessage('errors.corrupt'), previous: $exception);
        }

        // Shared read lock.
        flock($file, LOCK_SH);

        while (($row = fgetcsv($file, $length, $delimiter, $enclosure, $escape)) !== false)
        {
            // If header is true, jump to the next row.
            if ($header)
            {
                $header = false;
                continue;
            }

            if (!is_null($function))
            {
                $function($row);
            } else
            {
                $data[] = $row;
            }
        }

        // Release shared read lock and close the file.
        flock($file, LOCK_UN);
        fclose($file);

        return empty($data) && !is_null($function) ? true : $data;
    }
}