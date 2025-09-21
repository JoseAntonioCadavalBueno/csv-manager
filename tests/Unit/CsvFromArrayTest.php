<?php

namespace tests\Unit;

use CsvManager\Facades\Csv;
use PHPUnit\Framework\TestCase;

class CsvFromArrayTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        if (class_exists('Illuminate\Support\Facades\Storage'))
        {
            $app = new \Illuminate\Container\Container();
            \Illuminate\Support\Facades\Facade::setFacadeApplication($app);
            $app->singleton('config', function ()
            {
                return [
                    'filesystems.default' => 'public',
                    'filesystems.disks.public' => [
                        'driver'        => 'local',
                        'root'          => self::CSV_TEST_PATH,
                        'url'           => '/storage',
                        'visibility'    => 'public'
                    ]
                ];
            });

            $app->singleton('files', function ()
            {
                return new \Illuminate\Filesystem\Filesystem();
            });

            $app->singleton('filesystem', function($app)
            {
                return new \Illuminate\Filesystem\FilesystemManager($app);
            });
        }
    }

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
     * Unitary test that generate a csv file from array
     *
     * @test
     */
    public function test_generate_a_csv_file_from_array()
    {
        if (class_exists('Illuminate\Support\Facades\Storage')) {
            $filename = 'laravel_php.csv';
        } elseif (class_exists('Symfony\Component\Filesystem\Filesystem')) {
            $filename = 'symfony_php.csv';
        } else {
            $filename = self::CSV_TEST_PATH . 'native_php.csv';
        }
        $result = Csv::fromArray(self::RAW_DATA, $filename);
        $this->assertFileExists($result);
    }

    /**
     * Unitary test that generate a txt file from array
     *
     * @test
     */
    public function test_generate_a_txt_file_from_array()
    {
        if (class_exists('Illuminate\Support\Facades\Storage')) {
            $filename = 'laravel_php.txt';
        } elseif (class_exists('Symfony\Component\Filesystem\Filesystem')) {
            $filename = 'symfony_php.txt';
        } else {
            $filename = self::CSV_TEST_PATH . 'native_php.txt';
        }
        $result = Csv::fromArray(self::RAW_DATA, $filename);
        $this->assertFileExists($result);
    }
}
