<?php

namespace CsvManager\Contracts;

use SplFileObject;

interface IDriver
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
    ): void;

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
    ): array|bool;
}