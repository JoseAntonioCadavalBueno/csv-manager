<?php

namespace CsvManager\Sources;

use CsvManager\Contracts\ISource;
use CsvManager\Core\ConfigManager;
use CsvManager\Core\LanguageManager;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Traits\SourceValidator;

class UntrustedSource implements ISource
{
    use SourceValidator;

    const PATH_SANITIZE_REGEX = '/^[\p{L}\p{N}\.\-_ :\/\\\\]+$/u';

    protected ConfigManager $config;
    protected LanguageManager $language;

    private string  $filePath;
    private string  $filename;
    private ?string $disk;

    public function __construct(ConfigManager $config, LanguageManager $language, string $filePath, ?string $filename = null, ?string $disk = null)
    {
        $this->config   = $config;
        $this->language = $language;
        $this->disk     = $disk;

        if (!is_null($filename))
        {
            $this->filePath = rtrim($filePath, DIRECTORY_SEPARATOR);
            $this->filename = $filename;
        } else
        {
            $this->filePath = dirname($filePath);
            $hasExtension = pathinfo($filePath, PATHINFO_EXTENSION) !== '';
            $endWithSlash = str_ends_with($filePath, DIRECTORY_SEPARATOR);
            $this->filename =  $endWithSlash || !$hasExtension
                ? TrustedFylesystemSource::CSV_EXTENSION . '_' . uniqid() . '.' . TrustedFylesystemSource::CSV_EXTENSION
                : basename($filePath);
        }
    }

    /**
     * Returns the path or identifier for the data source.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->filePath;
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
        return $this->filePath . DIRECTORY_SEPARATOR . $this->filename;
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
     * @throws NotFoundFileException
     */
    public function validate(bool $fileMustExist = true): void
    {
        if (!static::isFilePathClean($this->getFullPath(), self::PATH_SANITIZE_REGEX))
        {
            throw new CorruptedFileException($this->language->getMessage('errors.corrupt'));
        }

        $allowedBasePaths = array_merge(
            [dirname(__DIR__, 5), sys_get_temp_dir()],
            $this->config->get('extra_allowed_paths', [])
        );

        if (!static::isWithinAllowedPaths($this->filePath, $allowedBasePaths))
        {
            throw new NotFoundFileException($this->language->getMessage('errors.not_found_2'));
        }

        if (!static::isAllowedExtension($this->filename, $this->config->get('allowed_extensions', TrustedFylesystemSource::DEFAULT_ALLOWED_EXTENSIONS)))
        {
            throw new CorruptedFileException($this->language->getMessage('errors.corrupt_2'));
        }

        if ($fileMustExist && !static::isReadableFile($this->getFullPath()))
        {
            throw new NotFoundFileException($this->language->getMessage('errors.not_found'));
        }
    }
}