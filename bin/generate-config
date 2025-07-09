#!/usr/bin/env php
<?php

define(
        'CSV_BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    );
define(
        'CSV_SOURCE_PATH', CSV_BASE_PATH . 'atomsk'
        . DIRECTORY_SEPARATOR . 'csv-manager'
        . DIRECTORY_SEPARATOR . 'config'
        . DIRECTORY_SEPARATOR . 'csv-manager.php'
    );
define(
        'DEFAULT_DESTINATION_PATH', CSV_BASE_PATH . '..'
        . DIRECTORY_SEPARATOR . 'config'
        . DIRECTORY_SEPARATOR
    );

require_once CSV_BASE_PATH . 'autoload.php';
require_once CSV_BASE_PATH . DIRECTORY_SEPARATOR . 'atomsk'
    . DIRECTORY_SEPARATOR . 'csv-manager'
    . DIRECTORY_SEPARATOR . 'config'
    . DIRECTORY_SEPARATOR . 'Config.php';

use CsvManager\config\Config;

if (!is_dir(DEFAULT_DESTINATION_PATH))
{
    if (!mkdir(DEFAULT_DESTINATION_PATH, 0755, true))
    {
        echo "ERROR: Could not create directory " . DEFAULT_DESTINATION_PATH . "\n";
        exit(1);
    }
}

Config::handle(realpath(CSV_SOURCE_PATH), $argv[1] ?? realpath(DEFAULT_DESTINATION_PATH . 'csv-manager.php'));
