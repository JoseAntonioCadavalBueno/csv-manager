<?php

namespace CsvManager\config;

use JetBrains\PhpStorm\NoReturn;

class config
{
    const AFIRMATIVE_INPUT = ['yes', 'sí', 'si', 's', 'y'];

    #[NoReturn] public static function handle(string $source, string $destination): void
    {
        $destination = trim($destination);
        if (!is_writable(dirname($destination)))
        {
            echo "Error: You do not have write permissions in the destination directory ({$destination}).";
            exit(1);
        }
        if (file_exists($destination))
        {
            echo "The configuration file already exists at the location {$destination}.\n";
            echo "Do you want to overwrite it with the default values? (Y/n):\n";
            $input = strtolower(trim(fgets(STDIN)));

            if (!in_array($input, self::AFIRMATIVE_INPUT))
            {
                echo "Operation canceled. The file has not been modified.\n";
                exit(0);
            }
        }

        if (!copy($source, $destination))
        {
            echo "Error: Failed to copy configuration file to {$destination}.";
            exit(1);
        }
        echo("Configuration file copied to the location {$destination}.\n");
        exit(0);
    }
}
