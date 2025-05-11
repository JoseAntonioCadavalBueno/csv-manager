<?php

namespace tests\Unit;

use PHPUnit\Framework\TestCase;
use src\Core\LanguageManager;
use src\Csv;
use src\Exceptions\CorruptedFileException;
use src\Exceptions\NotFoundFileException;
use src\Exceptions\OverflowException;

class CsvToArrayTest extends TestCase
{
    const CSV_TEST_PATH     = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'test-csv' . DIRECTORY_SEPARATOR;
    const BIGGER_CSV   = self::CSV_TEST_PATH . 'biggerTest.csv';
    const SMALLER_CSV  = self::CSV_TEST_PATH . 'smallerTest.csv';
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
        // Generate csv files for tests.
        $this->generateCsv(self::SMALLER_CSV, 10, 1);
        $this->generateCsv(self::BIGGER_CSV, 500000, 10);
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
     * Unitary test that try to process a not valid csv file.
     *
     * @test
     */
    public function test_csv_file_is_not_valid()
    {
        try {
            $result = Csv::toArray(realpath(self::CSV_TEST_PATH) . DIRECTORY_SEPARATOR . 'LollipopStreetFakeNumber');
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
                $result = Csv::toArray(realpath(self::CSV_TEST_PATH) . DIRECTORY_SEPARATOR . $maliciousInput);
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
}
