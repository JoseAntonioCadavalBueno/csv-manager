<?php

namespace tests\Benchmark;

use CsvManager\Models\CsvFile;

class CsvFileBench
{
    const CSV_BENCH_PATH     = __DIR__ . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . 'resources'
        . DIRECTORY_SEPARATOR . 'bench-files' . DIRECTORY_SEPARATOR;
    const BIGGER_CSV    = 'biggerTest.csv';
    const MEDIUM_CSV    = 'mediumTest.csv';
    const SMALLER_CSV   = 'smallerTest.csv';

    public function __construct()
    {
        $this->generateCsv(self::CSV_BENCH_PATH . self::SMALLER_CSV, 10, 1);
        $this->generateCsv(self::CSV_BENCH_PATH . self::MEDIUM_CSV, 50000, 1);
        $this->generateCsv(self::CSV_BENCH_PATH . self::BIGGER_CSV, 500000, 10);
    }

    public function __destruct()
    {
        $this->removeBenchFiles();
    }

    public function benchReadACompleteSmallerCsv(): void
    {
        $csv = new CsvFile(self::CSV_BENCH_PATH, self::SMALLER_CSV);

        $csv->readLines(0, 9);
        $csv->close();
    }

    public function benchReadACompleteMediumCsv(): void
    {
        $csv = new CsvFile(self::CSV_BENCH_PATH, self::MEDIUM_CSV);

        $lastLine = 49999;
        $index = 0;
        $chunkSize = 10000;
        while ($index < $lastLine)
        {
            $to = $index + $chunkSize;
            $csv->readLines($index, $to);
            $index = $to + 1;
        }
        $csv->close();
    }

    public function benchReadACompleteBiggerCsv(): void
    {
        $csv = new CsvFile(self::CSV_BENCH_PATH, self::BIGGER_CSV);

        $lastLine = 499999;
        $index = 0;
        $chunkSize = 5000;
        while ($index < $lastLine)
        {
            $to = $index + $chunkSize;
            $csv->readLines($index, $to);
            $index = $to + 1;
        }
        $csv->close();
    }

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

    private function removeBenchFiles(): void
    {
        $files = glob(realpath(self::CSV_BENCH_PATH) . '/*');
        foreach ($files as $file)
        {
            if (is_file($file))
            {
                unlink($file);
            }
        }
    }
}