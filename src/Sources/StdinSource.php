<?php

namespace CsvManager\Sources;

use CsvManager\Contracts\ISource;
use CsvManager\Core\Language;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Traits\SourceValidator;

class StdinSource implements ISource
{
    use SourceValidator;

    const DEFAULT_STDIN_PATH = 'php://stdin';

    protected Language $language;
    private ?string $filename;
    private ?string $disk;
    public function __construct(Language $language, ?string $filename = null, ?string $disk = null)
    {
        $this->language = $language;
        $this->disk     = $disk;
        $this->filename = $filename;
    }

    /**
     * Returns the path or identifier for the data source.
     *
     * @return string
     */
    public function getPath(): string
    {
        return self::DEFAULT_STDIN_PATH;
    }

    /**
     * Returns the filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Returns the fullpath for the data source.
     *
     * @return string
     */
    public function getFullPath(): string
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . $this->filename;
    }

    /**
     * Returns the disk where file is located (For Laravel Environments).
     *
     * @return string|null
     */
    public function getDisk(): ?string
    {
        return $this->disk;
    }

    /**
     * Validates that the source can be safely used.
     *
     * @param bool $fileMustExist
     * @return void
     * @throws CorruptedFileException
     */
    public function validate(bool $fileMustExist = true): void
    {
        if (!is_null($this->filename) && !static::isFilePathClean($this->filename, UntrustedSource::PATH_SANITIZE_REGEX))
        {
            throw new CorruptedFileException($this->language->getMessage('errors.corrupt'));
        }

        if (!is_null($this->filename))
        {
            $this->filename = $this->getSanitizedFilename($this->filename, UntrustedSource::PATH_SANITIZE_REGEX);
        }
    }
}