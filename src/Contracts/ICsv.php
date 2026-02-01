<?php

namespace CsvManager\Contracts;

use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use JsonException;

interface ICsv
{
    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

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
    public function toArray(
        ISource     $source,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\'
    ): array|bool;

    /**
     * A function that processes a csv file and converts it into a json.
     *
     * @param ISource       $source
     * @param bool          $header
     * @param callable|null $function
     * @param int|null      $length
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @param int           $flags
     * @return string|bool
     *
     * @throws CorruptedFileException
     * @throws JsonException
     * @throws NotFoundFileException
     * @throws OverflowException
     */
    public function toJson(
        ISource     $source,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\',
        int         $flags      = JSON_PRETTY_PRINT
    ): string|bool;
}