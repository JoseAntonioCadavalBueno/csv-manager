<?php

namespace CsvManager\Core;

use CsvManager\Traits\Operators;
use SplFileObject;
use Throwable;

class CsvAnalyzer
{
    use Operators;

    const DEFAULT_NAME_COLUMN   = 'column_%s';
    const BOOLEAN_VALUES        = ['true', 'false', '0', '1', 'yes', 'no', 'y', 'n'];
    const DELIMITER_VALUES      = [',', ';', '\t', '|'];
    const ENCLOSURE_VALUES      = ['"', "'"];

    /**
     * Function that make a partial analyze for csv file.
     *
     * @param string $filePath
     * @return array
     */
    public static function partialAnalyze(string $filePath): array
    {
        $meta = self::getMeta($filePath);
        return [
            'size'          => @filesize($filePath) ?: 0,
            'wrapper_type'  => $meta['wrapper_type']    ?? '',
            'stream_type'   => $meta['stream_type']     ?? '',
            'seekable'      => $meta['seekable']        ?? false,
            'timed_out'     => $meta['timed_out']       ?? false,
            'blocked'       => $meta['blocked']         ?? false,
            'last_modified' => date('Y-m-d H:i:s', @filemtime($filePath) ?: time())
        ];
    }

    /**
     * Function that make a full analyze for csv file.
     *
     * @param string        $filePath
     * @param bool          $header
     * @param string|null   $delimiter
     * @param string|null   $enclosure
     * @param string|null   $escape
     * @return array
     */
    public static function analyze(
        string  $filePath,
        bool    $header     = false,
        ?string $delimiter  = null,
        ?string $enclosure  = null,
        ?string $escape     = "\\"
    ): array
    {
        if ($handle = @fopen($filePath, 'r'))
        {
            $delimiter  = $delimiter ?? self::detectedChar($handle, self::DELIMITER_VALUES) ?: ',';
            $enclosure  = $enclosure ?? self::detectedChar($handle, self::ENCLOSURE_VALUES) ?: '"';
            $headerInfo = self::buildHeader($handle, $header, $delimiter, $enclosure, $escape);

            fclose($handle);
        } else
        {
            $headerInfo = [];
        }

        return array_merge(
            self::partialAnalyze($filePath),
            [
                'delimiter'     => $delimiter,
                'enclosure'     => $enclosure,
                'escape'        => $escape,
                'header'        => array_keys($headerInfo),
                'header_type'   => $headerInfo,
                'num_of_lines'  => self::countLines($filePath),
            ]
        );
    }

    /* ************************** */
    /* Protected helper functions */
    /* ************************** */

    /**
     * Returns meta-data of csv.
     *
     * @param string $filePath
     * @return array
     */
    protected static function getMeta(string $filePath): array
    {
        $meta = [];

        if ($file = @fopen($filePath, 'r'))
        {
            $meta = stream_get_meta_data($file);
            fclose($file);
        }

        return $meta;
    }

    /**
     * Build a typed header of the csv.
     *
     * @param resource  $handle
     * @param bool      $header
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @return array
     */
    protected static function buildHeader(
        $handle,
        bool    $header,
        string  $delimiter,
        string  $enclosure,
        string  $escape
    ): array
    {
        $result = [];
        rewind($handle);
        $firstRow = @fgetcsv(stream: $handle, separator: $delimiter, enclosure: $enclosure, escape: $escape) ?: [];

        if ($header)
        {
            $headerRow  = $firstRow;
            $firstRow   = @fgetcsv(stream: $handle, separator: $delimiter, enclosure: $enclosure, escape: $escape) ?: [];
        }

        foreach ($firstRow as $index => $column)
        {
            $key = $header
                ? $headerRow[$index]
                : sprintf(self::DEFAULT_NAME_COLUMN, $index);

            $value = 'string';

            if (is_numeric($column))
            {
                $value = str_contains($column, '.')
                    ? 'float'
                    : 'integer';
            } elseif (in_array(strtolower($column), self::BOOLEAN_VALUES))
            {
                $value = 'boolean';
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Calculate the number of lines.
     *
     * @param string $filePath
     * @return int
     */
    protected static function countLines(string $filePath): int
    {
        try
        {
            $file = new SplFileObject($filePath, 'r');
            $file->seek(PHP_INT_MAX);

            return $file->key() + 1;
        } catch (Throwable $exception)
        {
            return 0;
        }
    }
}
