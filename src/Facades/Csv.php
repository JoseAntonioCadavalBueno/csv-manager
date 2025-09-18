<?php

namespace CsvManager\Facades;

use CsvManager\Contracts\ICsv;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Integrations\LaravelCsv;
use CsvManager\Integrations\NativeCsv;
use CsvManager\Integrations\SymfonyCsv;

class Csv
{
    /** @var ICsv $instance */
    private static ICsv $instance;

    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

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
     */
    public static function toArray(
        string      $filePath,
        bool        $header     = false,
        ?callable   $function   = null,
        ?int        $length     = null,
        string      $delimiter  = ',',
        string      $enclosure  = '"',
        string      $escape     = '\\'
    ): array|bool
    {
        self::resolveInstance();

        return self::$instance::toArray(
            $filePath,
            $header,
            $function,
            $length,
            $delimiter,
            $enclosure,
            $escape
        );
    }

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
    ): string
    {
        self::resolveInstance();

        return self::$instance::fromArray(
            $data,
            $filename,
            $delimiter,
            $enclosure,
            $customPath
        );
    }

    /* ************************* */
    /* PRIVATE HELPERS FUNCTIONS */
    /* ************************* */

    /**
     * Configure the correct integration for ICsv.
     *
     * @return void
     */
    private static function resolveInstance(): void
    {
        if (!isset(self::$instance)) {
            if (class_exists('Illuminate\Support\Facades\Storage')) {
                self::$instance = new LaravelCsv();
            } elseif (class_exists('Symfony\Component\Filesystem\Filesystem')) {
                self::$instance = new SymfonyCsv();
            } else {
                self::$instance = new NativeCsv();
            }
        }
    }
}
