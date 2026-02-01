<?php

namespace src\Drivers;

use CsvManager\Contracts\IDriver;
use CsvManager\Contracts\ISource;
use CsvManager\Models\CsvFile;
use SplFileObject;

class StandardDriver implements IDriver
{
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
        $csv = new CsvFile(
            $source->getPath(),
            $source->getFilename(),
            false,
            $delimiter,
            $enclosure,
            $escape
        );

        foreach ($data as $row)
        {
            $csv->createLine($row);
        }

        $csv->close();
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
        $lines = [];
        $from = $header ? 1: 0;

        $csv = new CsvFile(
            $source->getPath(),
            $source->getFilename(),
            $header, $delimiter,
            $enclosure,
            $escape
        );

        if (is_null($function))
        {
            $lines = $csv->readLines($from, $csv->getNumOfLines() - 1);
        } else
        {
            do{
                $result = $csv->readLines($from);
                if (!empty($result))
                {
                    $lines[] = $result;
                }
                $from++;
            }
            while ($from < $csv->getNumOfLines() && !empty($result));
        }

        $csv->close();
        return $lines;
    }
}