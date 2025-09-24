<?php

namespace CsvManager\Integrations;

use CsvManager\Core\BaseCsv;
use CsvManager\Core\ConfigManager;
use CsvManager\Core\LanguageManager;
use CsvManager\Exceptions\CorruptedFileException;
use LogicException;
use SplFileObject;
use Symfony\Component\Filesystem\Filesystem;

class SymfonyCsv extends BaseCsv
{
    const SYMFONY_DIR = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;

    /**
     * A function that generates a CSV file from an array.
     *
     * @param array         $data
     * @param string|null   $filename
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string|null   $path
     * @param string|null   $disk
     * @return string
     * @throws CorruptedFileException
     */
    public static function fromArray(
        array   $data,
        ?string $filename   = null,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        ?string $path       = null,
        ?string $disk       = null
    ): string
    {
        $filename = self::generateFileName($filename);

        if (!is_null($disk))
        {
           throw new LogicException(LanguageManager::getMessage('errors.symfony_logic'));
        }

        $relativePath = !is_null($path)
            ? self::sanitizeFileName($path . DIRECTORY_SEPARATOR . $filename)
            : self::SYMFONY_DIR . $filename;

        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($relativePath));

        $file = new SplFileObject($relativePath, 'w');

        foreach ($data as $row) {
            $file->fputcsv($row, $delimiter, $enclosure);
        }

        return $relativePath;
    }
}