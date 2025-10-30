<?php

namespace CsvManager\Contracts;

use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;

interface ISource
{
    /**
     * Returns the path or identifier for the data source.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Returns the filename.
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Returns the fullpath for the data source.
     *
     * @return string
     */
    public function getFullPath(): string;

    /**
     * Returns the disk where file is located (For Laravel Environments).
     *
     * @return string|null
     */
    public function getDisk(): ?string;

    /**
     * Validates that the source can be safely used.
     *
     * @param bool $fileMustExist
     * @return void
     * @throws CorruptedFileException
     * @throws NotFoundFileException
     */
    public function validate(bool $fileMustExist = true): void;
}
