<?php

namespace tests\Feature;

use CsvManager\Models\CsvFile;
use PHPUnit\Framework\TestCase;

class ManageCsvTest extends TestCase
{
    const FILES_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'resources'
    . DIRECTORY_SEPARATOR . 'example-test-files' . DIRECTORY_SEPARATOR;
    const CSV_WITH_HEADER = 'csv-with-header.csv';

    const CONTENT = [
        [12,'Joe Pooh','joe','he is an editor'],
        [15, 'Mariah Lu', 'lu', 'she is an supervisor'],
        [5, 'Jon Fo', 'jon', 'he is a reader']
    ];

    /**
     * @test
     */
    public function try_to_get_all_content_from_csv(): void
    {
        $csv = new CsvFile(
            self::FILES_PATH,
            self::CSV_WITH_HEADER,
            true,
            ';',
            '"',
            '\\'
        );

        $lines = $csv->open()->readLines(1, 3);
        $csv->close();

        $this->assertCount(3, $lines);

        foreach (self::CONTENT as $index => $row) {
            list($id, $user, $code, $description) = $row;

            $this->assertEquals($id,            $lines[$index][0]);
            $this->assertEquals($user,          $lines[$index][1]);
            $this->assertEquals($code,          $lines[$index][2]);
            $this->assertEquals($description,   $lines[$index][3]);
        }
    }

    /**
     * @test
     */
    public function try_to_get_a_range_of_lines_from_csv(): void
    {
        $csv = new CsvFile(
            self::FILES_PATH,
            self::CSV_WITH_HEADER,
            true,
            ';',
            '"',
            '\\'
        );

        $lines = $csv->open()->readLines(2, 3);
        $csv->close();

        $this->assertCount(2, $lines);

        $index = 1;
        foreach ($lines as $row) {
            list($id, $user, $code, $description) = $row;

            $this->assertEquals($id,            self::CONTENT[$index][0]);
            $this->assertEquals($user,          self::CONTENT[$index][1]);
            $this->assertEquals($code,          self::CONTENT[$index][2]);
            $this->assertEquals($description,   self::CONTENT[$index][3]);
            $index++;
        }
    }

    /**
     * @test
     */
    public function try_to_get_a_line_from_csv(): void
    {
        $csv = new CsvFile(
            self::FILES_PATH,
            self::CSV_WITH_HEADER,
            true,
            ';',
            '"',
            '\\'
        );

        $lines = $csv->open()->readLines(2);
        $csv->close();

        $this->assertCount(1, $lines);

        $this->assertEquals(self::CONTENT[1][0], $lines[0][0]);
        $this->assertEquals(self::CONTENT[1][1], $lines[0][1]);
        $this->assertEquals(self::CONTENT[1][2], $lines[0][2]);
        $this->assertEquals(self::CONTENT[1][3], $lines[0][3]);
    }

    /**
     * @test
     */
    public function try_to_add_a_new_line_in_the_csv(): void
    {
        $temporalCsvName = 'csv-with-header-copy.csv';
        $temporalCsv = self::FILES_PATH . $temporalCsvName;
        copy(self::FILES_PATH . self::CSV_WITH_HEADER, $temporalCsv);

        $csv = new CsvFile(
            self::FILES_PATH,
            $temporalCsvName,
            true,
            ';',
            '"',
            '\\'
        );

        $newLine = [25, 'Flo Pooh', 'foo', 'this is a test.'];

        $csv->createLine($newLine);
        $lines = $csv->open()->readLines(4);
        $csv->close();

        $this->assertCount(1, $lines);

        $this->assertEquals($newLine[0], $lines[0][0]);
        $this->assertEquals($newLine[1], $lines[0][1]);
        $this->assertEquals($newLine[2], $lines[0][2]);
        $this->assertEquals($newLine[3], $lines[0][3]);

        // Tenemos que hacer unset al $csv para que se aplique la función __destruct() de este.
        unset($csv);
        unlink($temporalCsv);
    }
}