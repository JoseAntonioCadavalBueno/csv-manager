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
     * @param ISource       $source
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @return string
     * @throws CorruptedFileException
     * @throws NotFoundFileException
     */
    public static function fromArray(
        array   $data,
        ISource $source,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\'
    ): string;

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
     * @throws OverflowException
     * @throws NotFoundFileException
     * @throws CorruptedFileException
     */
    public static function toArray(
        ISource     $source,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\'
    ): array|bool;
}