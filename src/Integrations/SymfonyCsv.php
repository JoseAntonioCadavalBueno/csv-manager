<?php

namespace CsvManager\Integrations;

use CsvManager\Contracts\ISource;
use CsvManager\Core\BaseCsv;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use SplFileObject;
use Symfony\Component\Filesystem\Filesystem;

class SymfonyCsv extends BaseCsv
{
    /**
     * A function that generates a CSV file from an array.
     *
     * @param array $data
     * @param ISource $source
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return string
     * @throws CorruptedFileException|NotFoundFileException
     */
    public static function fromArray(
        array   $data,
        ISource $source,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = '\\'
    ): string
    {
        self::validateCsvChars($delimiter, $enclosure, $escape);

        $source->validate(false);

        $filesystem = new Filesystem();
        $filesystem->mkdir($source->getPath());

        $file = new SplFileObject($source->getFullPath(), 'w');

        foreach ($data as $row) {
            $file->fputcsv(
                self::arrayFlattenAndNormalize($row),
                $delimiter,
                $enclosure,
                $escape
            );
        }

        return $source->getFullPath();
    }
}