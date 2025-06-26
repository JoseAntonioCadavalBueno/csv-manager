<?php

namespace tests\Unit;

use PHPUnit\Framework\TestCase;
use CsvManager\src\Csv;

class CsvFromArrayNativeTest extends TestCase
{
    const CSV_TEST_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
        . 'resources' . DIRECTORY_SEPARATOR . 'test-csv' . DIRECTORY_SEPARATOR;

    const RAW_DATA = [
        ['ID', 'Name', 'Age', 'Country', 'Email', 'Description'],
        [1, 'User1', 18, 'Country1', 'user1@example.com', 'Sample text for large dataset.'],
        [2, 'User2', 22, 'Country2', 'user2@example.com', 'Sample text for large dataset.'],
        [3, 'User3', 32, 'Country3', 'user3@example.com', 'Sample text for large dataset.'],
        [4, 'User4', 30, 'Country4', 'user4@example.com', 'Sample text for large dataset.'],
        [5, 'User5', 25, 'Country5', 'user5@example.com', 'Sample text for large dataset.'],
    ];

    public function tearDown(): void
    {
        $files = glob(realpath(self::CSV_TEST_PATH) . '/*');

        foreach ($files as $file)
        {
            if (is_file($file))
            {
                unlink($file);
            }
        }
    }

    /**
     * Unitary test that generate a csv file from array in native php
     *
     * @test
     */
    public function test_generate_a_csv_file_from_array_in_native_php()
    {
        $result = Csv::fromArray(self::RAW_DATA, self::CSV_TEST_PATH . 'native_php.csv');
        $this->assertFileExists($result);
    }
}
