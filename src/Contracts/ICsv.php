<?php

namespace src\Contracts;

use src\Exceptions\CorruptedFileException;
use src\Exceptions\NotFoundFileException;
use src\Exceptions\OverflowException;

interface ICsv
{
    /* **************** */
    /* GETTER FUNCTIONS */
    /* **************** */

    /**
     * A getter function that returns the value of $freeMemory.
     *
     * @return int
     */
    public static function getFreeMemory(): int;

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
     * @param string|null   $customPath
     * @return string
     */
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $customPath = null
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