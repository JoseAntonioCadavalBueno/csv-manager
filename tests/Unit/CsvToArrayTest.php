<?php

namespace tests\Unit;

use CsvManager\Core\LanguageManager;
use CsvManager\Exceptions\CorruptedFileException;
use CsvManager\Exceptions\NotFoundFileException;
use CsvManager\Exceptions\OverflowException;
use CsvManager\Facades\Csv;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

class CsvToArrayTest extends TestCase
{
    const CSV_TEST_PATH     = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'test-csv' . DIRECTORY_SEPARATOR;
    const BIGGER_CSV    = self::CSV_TEST_PATH . 'biggerTest.csv';
    const BIGGER_TXT    = self::CSV_TEST_PATH . 'biggerTest.txt';
    const SMALLER_CSV   = self::CSV_TEST_PATH . 'smallerTest.csv';
    const SMALLER_TXT   = self::CSV_TEST_PATH . 'smallerTest.txt';
    const EMPTY_CSV     = self::CSV_TEST_PATH . 'emptyTest.csv';
    const EMPTY_TXT     = self::CSV_TEST_PATH . 'emptyTest.txt';

    const MALICIOUS_INPUTS  = [
        "test.csv; rm -rf /",
        "`ls -la`",
        "$(ls)",
        "php://filter/read=convert.base64-encode/resource=test.csv",
        "php://input",
        "\x00test.csv"
    ];

    /* ************************ */
    /* PRIVATE HELPER FUNCTIONS */
    /* ************************ */

    /**
     * Helper function that generate a csv file for tests.
     *
     * @param string    $filePath
     * @param int       $numLines
     * @param int       $descriptionLength
     * @return void
     */
    private function generateCsv(string $filePath, int $numLines, int $descriptionLength): void
    {
        $file = fopen($filePath, "w");
        fwrite($file,"\"ID\",\"Name\",\"Age\",\"Country\",\"Email\",\"Description\"\n");

        $buffer     = "";

        for ($i = 1; $i <= $numLines; $i++) {
            $longText = str_repeat("Sample text for large dataset. ", $descriptionLength);

            $buffer .= "\"$i\",\"User$i\",\"" . rand(18, 60) . "\",\"Country$i\",\"user$i@example.com\",\"$longText\"\n";

            if ($i % 10000 === 0) {
                fwrite($file, $buffer);
                $buffer = "";
            }
        }

        if (!empty($buffer)) {
            fwrite($file, $buffer);
        }

        fclose($file);
    }

    /* ****************** */
    /* TEST CONFIGURATION */
    /* ****************** */

    public function setUp(): void
    {
        // Generate csv and txt files for tests.
        $this->generateCsv(self::SMALLER_CSV, 10, 1);
        $this->generateCsv(self::SMALLER_TXT, 10, 1);
        $this->generateCsv(self::BIGGER_CSV, 500000, 10);
        $this->generateCsv(self::BIGGER_TXT, 500000, 10);
        $this->generateCsv(self::EMPTY_CSV, 0, 0);
        $this->generateCsv(self::EMPTY_TXT, 0, 0);

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

    /* ***** */
    /* TESTS */
    /* ***** */

    /**
     * Unitary test that processes an empty csv and return and empty array if dont use callable.
     *
     * @test
     */
    public function test_processes_empty_csv_and_return_empty_array_if_dont_use_callable()
    {
        $result = Csv::toArray(realpath(self::EMPTY_CSV), true);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Unitary test that processes an empty txt and return and empty array if dont use callable.
     *
     * @test
     */
    public function test_processes_empty_txt_and_return_empty_array_if_dont_use_callable()
    {
        $result = Csv::toArray(realpath(self::EMPTY_TXT), true);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Unitary test that processes an empty csv and return true if use callable.
     *
     * @test
     */
    public function test_processes_empty_csv_and_return_true_if_use_callable()
    {
        $result = Csv::toArray(realpath(self::EMPTY_CSV), true,
            function ()
            {
                return;
            });

        $this->assertTrue($result);
    }

    /**
     * Unitary test that processes an empty txt and return true if use callable.
     *
     * @test
     */
    public function test_processes_empty_txt_and_return_true_if_use_callable()
    {
        $result = Csv::toArray(realpath(self::EMPTY_TXT), true,
            function ()
            {
                return;
            });

        $this->assertTrue($result);
    }

    /**
     * Unitary test that processes csv of smaller size than memory limit when function is null.
     *
     * @test
     */
    public function test_processes_csv_of_smaller_size_than_memory_limit_when_function_is_null()
    {
        $result = Csv::toArray(realpath(self::SMALLER_CSV));

        $this->assertNotTrue($result);
        $this->assertIsArray($result);
        $this->assertCount(11, $result);
    }

    /**
     * Unitary test that processes txt of smaller size than memory limit when function is null.
     *
     * @test
     */
    public function test_processes_txt_of_smaller_size_than_memory_limit_when_function_is_null()
    {
        $result = Csv::toArray(realpath(self::SMALLER_TXT));

        $this->assertNotTrue($result);
        $this->assertIsArray($result);
        $this->assertCount(11, $result);
    }

    /**
     * Unitary test that processes csv of smaller size than memory limit when function is null.
     *
     * @test
     */
    public function test_processes_csv_of_smaller_size_than_memory_limit_when_function_is_null_with_header()
    {
        $result = Csv::toArray(realpath(self::SMALLER_CSV), true);

        $this->assertNotTrue($result);
        $this->assertIsArray($result);
        $this->assertCount(10, $result);
    }

    /**
     * Unitary test that processes csv of smaller size than memory limit when function is not null.
     *
     * @test
     */
    public function test_processes_csv_of_smaller_size_than_memory_limit_when_function_is_not_null()
    {
        $result = Csv::toArray(
            realpath(self::SMALLER_CSV),
            true,
            function (array $data) {
                $this->assertCount(6, $data);
            });

        $this->assertIsNotArray($result);
        $this->assertTrue($result);
    }

    /**
     * Unitary test that processes csv of bigger size than memory limit when function is null.
     *
     * @test
     */
    public function test_processes_csv_of_bigger_size_than_memory_limit_when_function_is_null()
    {
        try {
            $result = Csv::toArray(realpath(self::BIGGER_CSV));
        } catch (OverflowException $exception) {
            $this->assertEquals(500, $exception->getCode());
            $this->assertEquals(LanguageManager::getMessage('errors.overflow'), $exception->getMessage());
        }
    }

    /**
     * Unitary test that processes txt of bigger size than memory limit when function is null.
     *
     * @test
     */
    public function test_processes_txt_of_bigger_size_than_memory_limit_when_function_is_null()
    {
        try {
            $result = Csv::toArray(realpath(self::BIGGER_TXT));
        } catch (OverflowException $exception) {
            $this->assertEquals(500, $exception->getCode());
            $this->assertEquals(LanguageManager::getMessage('errors.overflow'), $exception->getMessage());
        }
    }

    /**
     * Unitary test that processes csv of bigger size than memory limit when function is not null.
     *
     * @test
     */
    public function test_processes_csv_of_bigger_size_than_memory_limit_when_function_is_not_null()
    {
        $result = Csv::toArray(
            realpath(self::BIGGER_CSV),
            false,
            function (array $data) {
                $this->assertCount(6, $data);
            });

        $this->assertIsNotArray($result);
        $this->assertTrue($result);
    }

    /**
     * Unitary test that processes txt of bigger size than memory limit when function is not null.
     *
     * @test
     */
    public function test_processes_txt_of_bigger_size_than_memory_limit_when_function_is_not_null()
    {
        $result = Csv::toArray(
            realpath(self::BIGGER_TXT),
            false,
            function (array $data) {
                $this->assertCount(6, $data);
            });

        $this->assertIsNotArray($result);
        $this->assertTrue($result);
    }

    /**
     * Unitary test that try to process a not valid csv file.
     *
     * @test
     */
    public function test_csv_file_is_not_found()
    {
        try {
            $result = Csv::toArray(realpath(self::CSV_TEST_PATH) . DIRECTORY_SEPARATOR . 'LollipopStreetFakeNumber.csv');
        } catch (NotFoundFileException $exception) {
            $this->assertEquals(404, $exception->getCode());
            $this->assertEquals(LanguageManager::getMessage('errors.not_found'), $exception->getMessage());
        }
    }

    /**
     * Unitary test that try to catch a received eval injection like csv file path.
     *
     * @test
     */
    public function test_received_eval_injection_like_csv_file_path()
    {
        foreach (self::MALICIOUS_INPUTS as $maliciousInput) {
            try {
                $result = Csv::toArray(realpath(self::CSV_TEST_PATH)
                    . DIRECTORY_SEPARATOR . '..;' . DIRECTORY_SEPARATOR
                    . '..' . DIRECTORY_SEPARATOR . $maliciousInput);
            } catch (CorruptedFileException $exception) {
                $this->assertEquals(415, $exception->getCode());
                $this->assertEquals(LanguageManager::getMessage('errors.corrupt'), $exception->getMessage());
            }
        }
    }

    /**
     * Unitary test that try to process a file with other extension.
     *
     * @test
     */
    public function test_received_a_file_with_other_extension()
    {
        $filePath = realpath(self::CSV_TEST_PATH) . DIRECTORY_SEPARATOR . 'biggerTest.xlsx';
        $file = fopen($filePath, 'w');
        fclose($file);
        try {
            $result = Csv::toArray($filePath);
        } catch (CorruptedFileException $exception) {
            $this->assertEquals(415, $exception->getCode());
            $this->assertEquals(LanguageManager::getMessage('errors.corrupt_2'), $exception->getMessage());
        }
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
            Csv::toArray(
                filePath: realpath(self::SMALLER_CSV),
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
            Csv::toArray(
                filePath: realpath(self::SMALLER_CSV),
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
            Csv::toArray(
                filePath: realpath(self::SMALLER_CSV),
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
            Csv::toArray(
                filePath: realpath(self::SMALLER_CSV),
                enclosure: 'à'
            );
        } catch (InvalidArgumentException $exception)
        {
            $this->assertEquals("The 'enclosure' character is not allowed.", $exception->getMessage());
        }
    }

    /**
     * Unitary test that try to use an invalid escape.
     *
     * @test
     */
    public function test_use_an_invalid_escape()
    {
        try
        {
            Csv::toArray(
                filePath: realpath(self::SMALLER_CSV),
                escape: 'à'
            );
        } catch (InvalidArgumentException $exception)
        {
            $this->assertEquals("The 'escape' character is not allowed.", $exception->getMessage());
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
            Csv::toArray(
                filePath: realpath(self::SMALLER_CSV),
                delimiter: '\t',
                enclosure: '\t',
                escape: '\t'
            );
        } catch (LogicException $exception)
        {
            $this->assertEquals("The 'delimiter', 'enclosure', and 'escape' must be different.", $exception->getMessage());
        }
    }
}
