<?php

namespace tests\Unit;

use CsvManager\Core\CsvCache;
use PHPUnit\Framework\TestCase;

class CsvCacheTest extends TestCase
{
    const FILES_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'resources'
    . DIRECTORY_SEPARATOR . 'example-test-files' . DIRECTORY_SEPARATOR;
    const CSV_WITH_HEADER = self::FILES_PATH . 'csv-with-header.csv';
    const CSV_WITHOUT_HEADER = self::FILES_PATH . 'csv-without-header.csv';
    const CSV_EMPTY = self::FILES_PATH . 'csv-empty.csv';

    /**
     * @test
     */
    public function try_to_generate_cache(): void
    {
        $file = fopen(self::CSV_WITH_HEADER, 'r');

        $meta = stream_get_meta_data($file);
        fclose($file);
        $meta = [
            'delimiter'     => ';',
            'enclosure'     => '"',
            'escape'        => '\\',
            'header'        => ['id', 'user', 'code', 'description'],
            'header_type'   => [
                'id'            => 'integer',
                'user'          => 'string',
                'code'          => 'string',
                'description'   => 'string'
            ],
            'num_of_lines'  => 4,
            'size'          => filesize(self::CSV_WITH_HEADER),
            'wrapper_type'  => $meta['wrapper_type'],
            'stream_type'   => $meta['stream_type'],
            'seekable'      => $meta['seekable'],
            'timed_out'     => $meta['timed_out'],
            'blocked'       => $meta['blocked'],
            'last_modified' => date('Y-m-d H:i:s', filemtime(self::CSV_WITH_HEADER) ?: time())
        ];

        $cache = new CsvCache(self::CSV_WITH_HEADER, true, ';', '"', '\\');

        $this->assertEquals($meta['delimiter'],        $cache->delimiter);
        $this->assertEquals($meta['enclosure'],        $cache->enclosure);
        $this->assertEquals($meta['escape'],           $cache->escape);
        $this->assertEquals($meta['header'],           $cache->header);
        $this->assertEquals($meta['header_type'],      $cache->header_type);
        $this->assertEquals($meta['num_of_lines'],     $cache->num_of_lines);
        $this->assertEquals($meta['size'],             $cache->size);
        $this->assertEquals($meta['wrapper_type'],     $cache->wrapper_type);
        $this->assertEquals($meta['stream_type'],      $cache->stream_type);
        $this->assertEquals($meta['seekable'],         $cache->seekable);
        $this->assertEquals($meta['timed_out'],        $cache->timed_out);
        $this->assertEquals($meta['blocked'],          $cache->blocked);
        $this->assertEquals($meta['last_modified'],    $cache->last_modified);
    }

    /**
     * @test
     */
    public function try_to_get_a_valid_cache(): void
    {
        $file = fopen(self::CSV_WITH_HEADER, 'r');
        $cachename = hash(CsvCache::HASHING_ALGORITHM, self::CSV_WITH_HEADER).'.cache.json';

        $size       = filesize(self::CSV_WITH_HEADER);
        $timestamp  = filemtime(self::CSV_WITH_HEADER);

        $meta = stream_get_meta_data($file);
        $cacheData = [
            'csv_hash'  => hash(CsvCache::HASHING_ALGORITHM, self::CSV_WITH_HEADER . "|$timestamp|$size"),
            'meta'      => [
                'delimiter'     => ';',
                'enclosure'     => '"',
                'escape'        => '\\',
                'header'        => ['id', 'user', 'code', 'description'],
                'header_type'   => [
                    'id'            => 'integer',
                    'user'          => 'string',
                    'code'          => 'string',
                    'description'   => 'string'
                ],
                'num_of_lines'  => 4,
                'size'          => filesize(self::CSV_WITH_HEADER),
                'wrapper_type'  => $meta['wrapper_type'],
                'stream_type'   => $meta['stream_type'],
                'seekable'      => $meta['seekable'],
                'timed_out'     => $meta['timed_out'],
                'blocked'       => $meta['blocked'],
                'last_modified' => date('Y-m-d H:i:s', filemtime(self::CSV_WITH_HEADER) ?: time())
            ]
        ];
        fclose($file);

        $cacheDir = sys_get_temp_dir() . CsvCache::CACHE_DIRECTORY;
        if (!is_dir($cacheDir))
        {
            mkdir($cacheDir, 0777, true);
        }

        file_put_contents($cacheDir . $cachename, json_encode($cacheData, JSON_PRETTY_PRINT));

        $cache = new CsvCache(self::CSV_WITH_HEADER, true, ';', '"', '\\');

        $this->assertEquals($cacheData['meta']['delimiter'],        $cache->delimiter);
        $this->assertEquals($cacheData['meta']['enclosure'],        $cache->enclosure);
        $this->assertEquals($cacheData['meta']['escape'],           $cache->escape);
        $this->assertEquals($cacheData['meta']['header'],           $cache->header);
        $this->assertEquals($cacheData['meta']['header_type'],      $cache->header_type);
        $this->assertEquals($cacheData['meta']['num_of_lines'],     $cache->num_of_lines);
        $this->assertEquals($cacheData['meta']['size'],             $cache->size);
        $this->assertEquals($cacheData['meta']['wrapper_type'],     $cache->wrapper_type);
        $this->assertEquals($cacheData['meta']['stream_type'],      $cache->stream_type);
        $this->assertEquals($cacheData['meta']['seekable'],         $cache->seekable);
        $this->assertEquals($cacheData['meta']['timed_out'],        $cache->timed_out);
        $this->assertEquals($cacheData['meta']['blocked'],          $cache->blocked);
        $this->assertEquals($cacheData['meta']['last_modified'],    $cache->last_modified);
    }

    /**
     * @test
     */
    public function try_to_get_a_invalid_cache_and_generate_new_one(): void
    {
        $file = fopen(self::CSV_WITH_HEADER, 'r');
        $cachename = hash(CsvCache::HASHING_ALGORITHM, self::CSV_WITH_HEADER).'.cache.json';

        $meta = stream_get_meta_data($file);
        $cacheData = [
            'csv_hash'  => 'corrupted_cache',
            'meta'      => [
                'delimiter'     => ';',
                'enclosure'     => '"',
                'escape'        => '\\',
                'header'        => ['id', 'user', 'code', 'description'],
                'header_type'   => [
                    'id'            => 'integer',
                    'user'          => 'string',
                    'code'          => 'string',
                    'description'   => 'string'
                ],
                'num_of_lines'  => 10,
                'size'          => filesize(self::CSV_WITH_HEADER),
                'wrapper_type'  => $meta['wrapper_type'],
                'stream_type'   => $meta['stream_type'],
                'seekable'      => $meta['seekable'],
                'timed_out'     => $meta['timed_out'],
                'blocked'       => $meta['blocked'],
                'last_modified' => date('Y-m-d H:i:s', filemtime(self::CSV_WITH_HEADER) ?: time())
            ]
        ];
        fclose($file);

        $cacheDir = sys_get_temp_dir() . CsvCache::CACHE_DIRECTORY;
        if (!is_dir($cacheDir))
        {
            mkdir($cacheDir, 0777, true);
        }

        file_put_contents($cacheDir . $cachename, json_encode($cacheData, JSON_PRETTY_PRINT));

        $cache = new CsvCache(self::CSV_WITH_HEADER, true, ';', '"', '\\');

        $this->assertEquals($cacheData['meta']['delimiter'],        $cache->delimiter);
        $this->assertEquals($cacheData['meta']['enclosure'],        $cache->enclosure);
        $this->assertEquals($cacheData['meta']['escape'],           $cache->escape);
        $this->assertEquals($cacheData['meta']['header'],           $cache->header);
        $this->assertEquals($cacheData['meta']['header_type'],      $cache->header_type);
        $this->assertNotEquals($cacheData['meta']['num_of_lines'],  $cache->num_of_lines);
        $this->assertEquals($cacheData['meta']['size'],             $cache->size);
        $this->assertEquals($cacheData['meta']['wrapper_type'],     $cache->wrapper_type);
        $this->assertEquals($cacheData['meta']['stream_type'],      $cache->stream_type);
        $this->assertEquals($cacheData['meta']['seekable'],         $cache->seekable);
        $this->assertEquals($cacheData['meta']['timed_out'],        $cache->timed_out);
        $this->assertEquals($cacheData['meta']['blocked'],          $cache->blocked);
        $this->assertEquals($cacheData['meta']['last_modified'],    $cache->last_modified);
    }

    /**
     * @test
     */
    public function try_to_regenerate_a_tmp_cache_file(): void
    {
        $file = fopen(self::CSV_WITH_HEADER, 'r');
        $cachename = hash(CsvCache::HASHING_ALGORITHM, self::CSV_WITH_HEADER).'.cache.json.tmp';

        $size       = filesize(self::CSV_WITH_HEADER);
        $timestamp  = filemtime(self::CSV_WITH_HEADER);

        $meta = stream_get_meta_data($file);
        $cacheData = [
            'csv_hash'  => hash(CsvCache::HASHING_ALGORITHM, self::CSV_WITH_HEADER . "|$timestamp|$size"),
            'meta'      => [
                'delimiter'     => ';',
                'enclosure'     => '"',
                'escape'        => '\\',
                'header'        => ['id', 'user', 'code', 'description'],
                'header_type'   => [
                    'id'            => 'integer',
                    'user'          => 'string',
                    'code'          => 'string',
                    'description'   => 'string'
                ],
                'num_of_lines'  => 4,
                'size'          => filesize(self::CSV_WITH_HEADER),
                'wrapper_type'  => $meta['wrapper_type'],
                'stream_type'   => $meta['stream_type'],
                'seekable'      => $meta['seekable'],
                'timed_out'     => $meta['timed_out'],
                'blocked'       => $meta['blocked'],
                'last_modified' => date('Y-m-d H:i:s', filemtime(self::CSV_WITH_HEADER) ?: time())
            ]
        ];
        fclose($file);

        $cacheDir = sys_get_temp_dir() . CsvCache::CACHE_DIRECTORY;
        if (!is_dir($cacheDir))
        {
            mkdir($cacheDir, 0777, true);
        }

        file_put_contents($cacheDir . $cachename, json_encode($cacheData, JSON_PRETTY_PRINT));

        $cache = new CsvCache(self::CSV_WITH_HEADER, true, ';', '"', '\\');

        $this->assertEquals($cacheData['meta']['delimiter'],        $cache->delimiter);
        $this->assertEquals($cacheData['meta']['enclosure'],        $cache->enclosure);
        $this->assertEquals($cacheData['meta']['escape'],           $cache->escape);
        $this->assertEquals($cacheData['meta']['header'],           $cache->header);
        $this->assertEquals($cacheData['meta']['header_type'],      $cache->header_type);
        $this->assertEquals($cacheData['meta']['num_of_lines'],     $cache->num_of_lines);
        $this->assertEquals($cacheData['meta']['size'],             $cache->size);
        $this->assertEquals($cacheData['meta']['wrapper_type'],     $cache->wrapper_type);
        $this->assertEquals($cacheData['meta']['stream_type'],      $cache->stream_type);
        $this->assertEquals($cacheData['meta']['seekable'],         $cache->seekable);
        $this->assertEquals($cacheData['meta']['timed_out'],        $cache->timed_out);
        $this->assertEquals($cacheData['meta']['blocked'],          $cache->blocked);
        $this->assertEquals($cacheData['meta']['last_modified'],    $cache->last_modified);
    }
}