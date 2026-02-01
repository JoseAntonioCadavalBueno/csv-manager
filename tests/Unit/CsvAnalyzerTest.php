<?php

namespace tests\Unit;

use CsvManager\Core\CsvAnalyzer;
use PHPUnit\Framework\TestCase;
class CsvAnalyzerTest extends TestCase
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
    public function try_to_analyze_a_csv_with_header(): void
    {
        $meta = CsvAnalyzer::analyze(self::CSV_WITH_HEADER,true,';', '"');

        $this->assertEquals(';', $meta['delimiter']);
        $this->assertEquals('"', $meta['enclosure']);
        $this->assertEquals('\\', $meta['escape']);
        $this->assertEquals(['id','user','code','description'], $meta['header']);
        $this->assertEquals([
                'id'            => 'integer',
                'user'          => 'string',
                'code'          => 'string',
                'description'   => 'string'
            ], $meta['header_type']);
        $this->assertEquals(4, $meta['num_of_lines']);
    }

    /**
     * @test
     */
    public function try_to_analyze_a_csv_without_header(): void
    {
        $meta = CsvAnalyzer::analyze(self::CSV_WITHOUT_HEADER,false,';', '"');

        $this->assertEquals(';', $meta['delimiter']);
        $this->assertEquals('"', $meta['enclosure']);
        $this->assertEquals('\\', $meta['escape']);
        $this->assertEquals(['column_0','column_1','column_2','column_3'], $meta['header']);
        $this->assertEquals([
            'column_0'  => 'integer',
            'column_1'  => 'string',
            'column_2'  => 'string',
            'column_3'  => 'string'
        ], $meta['header_type']);
        $this->assertEquals(3, $meta['num_of_lines']);
    }

    /**
     * @test
     */
    public function try_to_analyze_a_csv_empty(): void
    {
        $meta = CsvAnalyzer::analyze(self::CSV_EMPTY,true,';', '"');

        $this->assertEquals(';', $meta['delimiter']);
        $this->assertEquals('"', $meta['enclosure']);
        $this->assertEquals('\\', $meta['escape']);
        $this->assertEmpty($meta['header']);
        $this->assertEmpty($meta['header_type']);
        $this->assertEquals(1, $meta['num_of_lines']);
    }

    /**
     * @test
     */
    public function try_to_analyze_a_csv_but_not_exist(): void
    {
        $meta = CsvAnalyzer::analyze(self::FILES_PATH . 'hello-world.csv',true,';', '"');

        $this->assertEquals(';', $meta['delimiter']);
        $this->assertEquals('"', $meta['enclosure']);
        $this->assertEquals('\\', $meta['escape']);
        $this->assertEmpty($meta['header']);
        $this->assertEmpty($meta['header_type']);
        $this->assertEquals(0, $meta['num_of_lines']);
    }

    /**
     * @test
     */
    public function try_to_analyze_a_csv_and_try_to_detect_delimiter_and_enclosure(): void
    {
        $meta = CsvAnalyzer::analyze(self::CSV_WITH_HEADER,true);
        $this->assertEquals(';', $meta['delimiter']);
        $this->assertEquals('"', $meta['enclosure']);
        $this->assertEquals('\\', $meta['escape']);
        $this->assertEquals(['id','user','code','description'], $meta['header']);
        $this->assertEquals([
            'id'            => 'integer',
            'user'          => 'string',
            'code'          => 'string',
            'description'   => 'string'
        ], $meta['header_type']);
        $this->assertEquals(4, $meta['num_of_lines']);
    }
}
