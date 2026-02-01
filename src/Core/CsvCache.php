<?php

namespace CsvManager\Core;

use CsvManager\Traits\HandleFiles;
use JsonException;
use LogicException;
use RuntimeException;

/**
 * @property string     $delimiter
 * @property string     $enclosure
 * @property string     $escape
 * @property array      $header
 * @property array      $header_type
 * @property int        $size
 * @property int        $num_of_lines
 * @property string     $wrapper_type
 * @property string     $stream_type
 * @property bool       $seekable
 * @property bool       $timed_out
 * @property bool       $blocked
 * @property string     $last_modified
 */
class CsvCache
{
    use HandleFiles;
    const HASHING_ALGORITHM = 'sha256';
    const CACHE_DIRECTORY = '/csv_cache/';
    const LOCKED_TIME_OUT = 300;
    private string $cachePath;
    private string $csvHash;
    private string $csvPath;
    private bool   $isDirty = false;

    private array $meta = [];

    public function __construct(
        string  $fullPath,
        bool    $header     = false,
        string  $delimiter  = ",",
        string  $enclosure  = '"',
        string  $escape     = "\\"
    )
    {
        $this->csvPath      = realpath($fullPath);
        $this->cachePath    = self::cachePath($this->csvPath);
        $this->csvHash      = self::csvHash($this->csvPath);

        $this->resolveCache($header, $delimiter, $enclosure, $escape);
    }

    public function __destruct()
    {
        if ($this->isDirty)
        {
            $this->csvHash = self::csvHash($this->csvPath);
            $this->meta = array_merge($this->meta, CsvAnalyzer::partialAnalyze($this->csvPath));

            $this->createOrUpdateCache($this->meta);
        }
    }

    /* **************** */
    /* public functions */
    /* **************** */

    public function __get(string $name): mixed
    {
        return $this->meta[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->isDirty      = true;
        $this->meta[$name]  = $value;
    }

    /* ************************ */
    /* Private helper functions */
    /* ************************ */

    /**
     * Resolve the cache.
     *
     * @param bool      $header
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @return void
     */
    private function resolveCache(bool $header, string $delimiter, string $enclosure, string $escape): void
    {
        $meta = $this->getCacheMeta();
        if ($meta === false)
        {
            $meta = CsvAnalyzer::analyze($this->csvPath, $header, $delimiter, $enclosure, $escape);
            $this->createOrUpdateCache($meta);
        }
        $this->meta = $meta;
    }

    /**
     * Create or update the cache from csv.
     *
     * @param array $meta
     * @return void
     */
    private function createOrUpdateCache(array $meta): void
    {
        $tmpCacheFile = "$this->cachePath.tmp";
        try
        {
            $cache = json_encode([
                'csv_hash'  => $this->csvHash,
                'meta'      => $meta
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );

            file_put_contents($tmpCacheFile, $cache, LOCK_EX);

            try
            {
                $lockFile = $this->acquireLock($this->cachePath, 'c+', LOCK_EX | LOCK_NB);

                if ($lockFile)
                {
                    rename($tmpCacheFile, $this->cachePath);
                    $this->releaseLock($lockFile);
                } else
                {
                    file_put_contents($this->cachePath, $cache);
                    self::deleteFiles([$tmpCacheFile]);
                }
            } catch (RuntimeException $exception){}
        } catch (JsonException)
        {
            self::deleteFiles([$tmpCacheFile, $this->cachePath]);
        }
    }

    /**
     * Returns the meta of the cache or false.
     *
     * @return array|false
     */
    private function getCacheMeta(): array|false
    {
        $cache          = false;
        $tmpCacheFile   = "$this->cachePath.tmp";

        try
        {
            if (file_exists($tmpCacheFile))
            {
                $this->handleLockFile($tmpCacheFile, $cache);
                if ($cache !== false)
                {
                    $this->createOrUpdateCache($cache);
                }
            } else
            {
                $this->handleLockFile($this->cachePath, $cache);
            }
        } catch (JsonException)
        {
            self::deleteFiles([$tmpCacheFile, $this->cachePath]);
            $cache = false;
        }

        return $cache;
    }

    /**
     * Handle a lock cache file.
     *
     * @param string        $filePath
     * @param array|bool    $cache
     * @return void
     * @throws JsonException
     */
    private function handleLockFile(string $filePath, array|bool &$cache): void
    {
        $lockFile = $this->acquireLock($filePath);

        if ($lockFile)
        {
            $rawCache = json_decode(
                json: file_get_contents($filePath),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            if ($rawCache['csv_hash'] === $this->csvHash)
            {
                $cache = $rawCache['meta'];
            }
            $this->releaseLock($lockFile);
        }
    }

    /**
     * Handle the hash generated from csv file.
     *
     * @param string $realPath
     * @return string
     * @throws LogicException
     */
    private static function csvHash(string $realPath): string
    {
        $size       = filesize($realPath);
        $timestamp  = filemtime($realPath);
        if ($size === false || $timestamp === false)
        {
            throw new LogicException("Cannot read the csv file");
        }

        return self::hash("$realPath|$timestamp|$size");
    }

    /**
     * Returns a hash from input $key.
     *
     * @param string $key
     * @return string
     */
    private static function hash(string $key): string
    {
        return hash(self::HASHING_ALGORITHM, $key);
    }

    /**
     * returns a full path for the cache.
     *
     * @param string $realPath
     * @return string
     */
    private static function cachePath(string $realPath): string
    {
        $cacheDir = sys_get_temp_dir() . self::CACHE_DIRECTORY;
        if (!is_dir($cacheDir))
        {
            mkdir($cacheDir, 0777, true);
        }

        return $cacheDir . self::hash($realPath) . '.cache.json';
    }

    /**
     * Delete a group of files.
     *
     * @param array $files
     * @return void
     */
    private static function deleteFiles(array $files): void
    {
        foreach ($files as $file)
        {
            if (file_exists($file))
            {
                unlink($file);
            }
        }
    }
}
