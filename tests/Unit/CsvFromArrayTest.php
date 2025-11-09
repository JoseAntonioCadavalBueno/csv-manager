<?php

namespace tests\Unit;

use CsvManager\Facades\Csv;
use InvalidArgumentException;
use LogicException;
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

    const NOT_NORMALIZED_DATA = [
        ['ID', 'Name', 'Age', 'Country', 'Email', 'Description'],
        [1, 'User1', 18, 'Country1', "user1@\nexample.com", ['Sample text for large dataset.']],
        [2, ['User2'], 22.48, false, '\\ruser2@example.com    ', 'Sample text for\\nlarge dataset.'],
        [3, 'User3', 32, true, ['user3@\nexample.com'], null],
        [4, ['  User4 ', [30, 'Country4'], ['user4@example.com', "Sample text\rfor large dataset."]]],
        [5, "\nUser5\n", 25, [['Country5']], '\nuser5@example.com\n', ' Sample text for large dataset. '],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        if (class_exists('Illuminate\Support\Facades\Storage'))
        {
            $path = realpath(self::CSV_TEST_PATH);
            $app = new \Illuminate\Foundation\Application();
            \Illuminate\Support\Facades\Facade::setFacadeApplication($app);

            $app->instance('path.storage', $path);

            $app->singleton('config', function () use ($path)
            {
                return [
                    'filesystems.default' => 'public',
                    'filesystems.disks.public' => [
                        'driver'        => 'local',
                        'root'          =>  $path,
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

    /**
     * Unitary test that generate a csv file from not normalized array
     *
     * @test
     */
    public function test_generate_a_csv_file_from_not_normalized_array()
    {
        if (class_exists('Illuminate\Support\Facades\Storage')) {
            $filename = 'laravel_php.csv';
        } elseif (class_exists('Symfony\Component\Filesystem\Filesystem')) {
            $filename = 'symfony_php.csv';
        } else {
            $filename = self::CSV_TEST_PATH . 'native_php.csv';
        }
        $result = Csv::fromArray(self::NOT_NORMALIZED_DATA, $filename);
        $this->assertFileExists($result);
    }

    /**
     * Unitary test that generate a txt file from not normalized array
     *
     * @test
     */
    public function test_generate_a_txt_file_from_not_normalized_array()
    {
        if (class_exists('Illuminate\Support\Facades\Storage')) {
            $filename = 'laravel_php.txt';
        } elseif (class_exists('Symfony\Component\Filesystem\Filesystem')) {
            $filename = 'symfony_php.txt';
        } else {
            $filename = self::CSV_TEST_PATH . 'native_php.txt';
        }
        $result = Csv::fromArray(self::NOT_NORMALIZED_DATA, $filename);
        $this->assertFileExists($result);
    }

    /**
     * Unitary test that try to use an invalid delimiter.
     *
     * @test
     */
    public function test_use_an_invalid_delimiter()
    {
        try
        {
            Csv::fromArray(
                data: [],
                filename: self::CSV_TEST_PATH . 'native_php.txt',
                delimiter: 'à'
            );
        } catch (InvalidArgumentException $exception)
        {
            $this->assertEquals("The 'delimiter' character is not allowed.", $exception->getMessage());
        }
    }

    /**
     * Unitary test that try to use a carriage return like delimiter.
     *
     * @test
     */
    public function test_use_a_carriage_return_like_delimiter()
    {
        try
        {
            Csv::fromArray(
                data: [],
                filename: self::CSV_TEST_PATH . 'native_php.txt',
                delimiter: '\r'
            );
        } catch (InvalidArgumentException $exception)
        {
            $this->assertEquals("The 'delimiter' character is not allowed.", $exception->getMessage());
        }
    }

    /**
     * Unitary test that try to use a newline char like delimiter.
     *
     * @test
     */
    public function test_use_a_newline_char_like_delimiter()
    {
        try
        {
            Csv::fromArray(
                data: [],
                filename: self::CSV_TEST_PATH . 'native_php.txt',
                delimiter: '\n'
            );
        } catch (InvalidArgumentException $exception)
        {
            $this->assertEquals("The 'delimiter' character is not allowed.", $exception->getMessage());
        }
    }

    /**
     * Unitary test that try to use an invalid enclosure.
     *
     * @test
     */
    public function test_use_an_invalid_enclosure()
    {
        try
        {
            Csv::fromArray(
                data: [],
                filename: self::CSV_TEST_PATH . 'native_php.txt',
                enclosure: 'à'
            );
        } catch (InvalidArgumentException $exception)
        {
            $this->assertEquals("The 'enclosure' character is not allowed.", $exception->getMessage());
        }
    }

    /**
     * Unitary test that try to use the same char for delimiter, escape and enclosure.
     *
     * @test
     */
    public function test_use_the_same_char_for_delimiter_escape_and_enclosure()
    {
        try
        {
            Csv::fromArray(
                data: [],
                filename: self::CSV_TEST_PATH . 'native_php.txt',
                delimiter: '\\',
                enclosure: '\\',
            );
        } catch (LogicException $exception)
        {
            $this->assertEquals("The 'delimiter', 'enclosure', and 'escape' must be different.", $exception->getMessage());
        }
    }
}
