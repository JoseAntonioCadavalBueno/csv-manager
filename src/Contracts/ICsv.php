<?php

namespace CsvManager\Contracts;

use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;

interface ICsv
{
    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

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
    ): string;

    /**
     * A function that processes a csv file and converts it into an array.
     *
     * @param string        $filePath
     * @param bool          $header
     * @param callable|null $function
     * @param int|null      $length
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @return array|bool
     * @throws OverflowException
     * @throws NotFoundFileException
     * @throws CorruptedFileException
     */
    public static function toArray(
        string      $filePath,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\'
    ): array|bool;
}