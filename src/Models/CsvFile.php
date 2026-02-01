<?php

namespace CsvManager\Models;

use CsvManager\Core\CsvCache;
use CsvManager\Traits\HandleFiles;
use CsvManager\Traits\Operators;
use SplFileObject;

class CsvFile
{
    use Operators, HandleFiles;

    const B = 'B';
    const KB = 'KB';
    const MB = 'MB';
    const GB = 'GB';
    const TB = 'TB';
    const PB = 'PB';
    const EB = 'EB';
    const ZB = 'ZB';
    const YB = 'YB';

    const ALLOW_UNITS = [
        self::B => 0, self::KB => 1, self::MB => 2, self::GB => 3, self::TB => 4,
        self::PB => 5, self::EB => 6, self::ZB => 7, self::YB => 8
    ];
    protected string    $filePath;
    protected string    $realPath;
    protected string    $filename;
    protected CsvCache  $cache;

    private ?SplFileObject $handle = null;
    /** @var resource|false $originalCsv */
    private $originalCsv = false;
    private string $tmpFilePath;

    public function __construct(
        string  $filePath,
        string  $filename,
        bool    $header     = false,
        string  $delimiter  = ',',
        string  $enclosure  = '"',
        string  $escape     = "\\"
    )
    {
        $this->filename = $filename;
        $this->filePath = realpath($filePath);

        $this->realPath = $this->filePath . DIRECTORY_SEPARATOR . $this->filename;
        $this->cache    = self::instanceCache($this->realPath, $header, $delimiter, $enclosure, $escape);
        $this->tmpFilePath = "$this->realPath.tmp";
    }

    public function __destruct()
    {
        if (!is_null($this->handle))
        {
            $this->close();
        }
    }

    /* ******************** */
    /* Essentials Functions */
    /* ******************** */

    /**
     * Open the csv file and handle like class property until close() is used.
     *
     * @param string $mode
     * @return $this
     */
    public function open(string $mode = 'c+'): static
    {
        if (is_null($this->handle))
        {
            $this->originalCsv = $this->acquireLock($this->realPath);
            if ($this->originalCsv)
            {
                copy($this->realPath, $this->tmpFilePath);
            }

            $this->handle = new SplFileObject($this->tmpFilePath, $mode);
            $this->handle->setFlags(SplFileObject::READ_CSV);

            if (!$this->originalCsv && !empty($this->getHeader()))
            {
                $this->handle->fputcsv(
                    $this->getHeader(),
                    $this->getDelimiter(),
                    $this->getEnclosure(),
                    $this->getEscape()
                );
            }
        }
        return $this;
    }

    /**
     * Close the csv file.
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->originalCsv)
        {
            $this->releaseLock($this->originalCsv);
            $this->originalCsv = false;
        }
        rename($this->tmpFilePath, $this->realPath);
        $this->handle = null;
    }

    /* ************** */
    /* CRUD functions */
    /* ************** */

    /**
     * Create a new line in the end of csv.
     *
     * @param array $row
     * @return $this
     */
    public function createLine(array $row): static
    {
        $this->ensureOpen();

        $this->handle->seek(PHP_INT_MAX);

        // Check if the last line has \n in the end.
        $pos = $this->handle->ftell();
        if ($pos > 0)
        {
            $this->handle->fseek($pos -1);
            $lastChar = $this->handle->fread(1);

            if ($lastChar !== "\n")
            {
                $this->handle->fseek(0, SEEK_END);
                $this->handle->fwrite("\n");
            }
        }

        $this->handle->fputcsv($row, $this->getDelimiter(), $this->getEnclosure(), $this->getEscape());

        $this->cache->num_of_lines++;

        return $this;
    }

    /**
     * Returns a group of lines.
     *
     * @param int $from
     * @param int|null $to
     * @return array
     */
    public function readLines(int $from = 0, ?int $to = null): array
    {
        $this->ensureOpen();

        $lines = [];
        $to ??= $from;

        $this->handle->seek($from);

        for ($i = $from; $i <= $to; $i++)
        {
            $row = $this->handle->fgetcsv($this->getDelimiter(), $this->getEnclosure(), $this->getEscape());
            if ($row === false)
            {
                break;
            }
            $lines[] = $row;
        }

        return $lines;
    }

    /* *********************** */
    /* Public Getter functions */
    /* *********************** */

    /**
     * Returns the directory of csv.
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Returns the full-path of csv.
     *
     * @return string
     */
    public function getRealPath():string
    {
        return $this->realPath;
    }

    /**
     * Returns the filename of csv.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Returns the delimiter of csv.
     *
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->cache->delimiter;
    }

    /**
     * Returns the enclosure of csv.
     *
     * @return string
     */
    public function getEnclosure(): string
    {
        return $this->cache->enclosure;
    }

    /**
     * Returns the escape of csv.
     *
     * @return string
     */
    public function getEscape(): string
    {
        return $this->cache->escape;
    }

    /**
     * Returns the header of csv.
     *
     * @return array
     */
    public function getHeader(): array
    {
        return $this->cache->header;
    }

    /**
     * Returns the number of lines.
     *
     * @return int|null
     */
    public function getNumOfLines(): ?int
    {
        return $this->cache->num_of_lines;
    }

    /**
     * Returns the size of csv.
     *
     * @param string $unit
     * @return float
     */
    public function getSize(string $unit = self::B): float
    {
        return self::convertStorageUnit($this->cache->size ?? 0, self::B, $unit);
    }

    /**
     * Returns the wrapper type of csv.
     *
     * @return string
     */
    public function getWrapperType(): string
    {
        return $this->cache->wrapper_type;
    }

    /**
     * Returns the stream type of csv.
     *
     * @return string
     */
    public function getStreamType(): string
    {
        return $this->cache->stream_type;
    }

    /**
     * Returns true if the csv is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->cache->seekable;
    }

    /**
     * Returns true if the csv is timed out.
     *
     * @return bool
     */
    public function isTimedOut(): bool
    {
        return $this->cache->timed_out;
    }

    /**
     * Returns true if the csv is blocked.
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->cache->blocked;
    }

    /**
     * Returns the last modified date of csv.
     *
     * @return string
     */
    public function getLastModified(): string
    {
        return $this->cache->last_modified;
    }

    /* ************************* */
    /* Private helpers functions */
    /* ************************* */

    /**
     * Instance a CsvCache.
     *
     * @param string    $fullPath
     * @param bool      $header
     * @param string    $delimiter
     * @param string    $enclosure
     * @param string    $escape
     * @return CsvCache
     */
    private static function instanceCache(
        string  $fullPath,
        bool    $header,
        string  $delimiter,
        string  $enclosure,
        string  $escape
    ): CsvCache
    {
        return new CsvCache($fullPath, $header, $delimiter, $enclosure, $escape);
    }

    /**
     * Open the csv if handle is null.
     *
     * @return void
     */
    private function ensureOpen(): void
    {
        if (is_null($this->handle))
        {
            $this->open();
        }
    }
}
