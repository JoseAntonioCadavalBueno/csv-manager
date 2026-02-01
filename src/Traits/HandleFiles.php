<?php

namespace CsvManager\Traits;

use CsvManager\Core\CsvCache;
use RuntimeException;

trait HandleFiles
{
    /**
     * Try to lock a file.
     *
     * @param   string          $filePath
     * @param   string          $mode
     * @param   int             $operation
     * @return  resource|bool
     * @throws RuntimeException
     */
    private function acquireLock(
        string      $filePath,
        string      $mode       = 'r',
        int         $operation  = LOCK_SH
    )
    {
        $file = @fopen($filePath, $mode);

        if ($file === false)
        {
            return false;
        }

        $wait       = 10000;
        $maxWait    = 200000;
        $startTime  = time();

        while ((time() - $startTime) < CsvCache::LOCKED_TIME_OUT)
        {
            if (flock($file, $operation))
            {
                return $file;
            }

            usleep($wait);
            $wait = min($wait * 2, $maxWait);
        }

        throw new RuntimeException("Timeout acquiring lock on file: {$filePath}");
    }

    /**
     * Unlock a file.
     *
     * @param resource $file
     * @return void
     */
    private function releaseLock($file): void
    {
        flock($file, LOCK_UN);
        fclose($file);
    }
}
