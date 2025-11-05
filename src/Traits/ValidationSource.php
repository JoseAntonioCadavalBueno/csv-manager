<?php

namespace CsvManager\Traits;

trait ValidationSource
{
    /**
     * Check if exist and is valid file.
     *
     * @param string $filePath
     * @return bool
     */
    protected static function isReadableFile(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * Check if the filename is clean.
     *
     * @param string $filePath
     * @param string $regex
     * @return bool
     */
    protected static function isFilePathClean(string $filePath, string $regex): bool
    {
        return preg_match($regex, $filePath) === 1;
    }

    /**
     * Check if the filePath is valid.
     *
     * @param string    $filePath
     * @param array     $allowedBasePaths
     * @return bool
     */
    protected static function isWithinAllowedPaths(string $filePath, array $allowedBasePaths): bool
    {
        $dir = dirname($filePath);
        $realDir = realpath($dir);

        if (!$realDir)
        {
            return false;
        }

        foreach ($allowedBasePaths as $allowedBasePath)
        {
            if (str_starts_with($realDir, $allowedBasePath))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the file's extension is allowed.
     *
     * @param string    $filePath
     * @param array     $allowedExtensions
     * @return bool
     */
    protected static function isAllowedExtension(string $filePath, array $allowedExtensions): bool
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        return !empty($extension) && in_array(strtolower($extension), $allowedExtensions);
    }

    /**
     * Function that sanitizes and return the filename variable to avoid unexpected results.
     *
     * @param string $filename
     * @param string $regex
     * @return string
     */
    protected function getSanitizedFilename(string $filename, string $regex): string
    {
        return preg_replace($regex, '', $filename);
    }
}
