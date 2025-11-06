<?php

namespace CsvManager;

use CsvManager\Facades\Csv as aliasCsv;

@trigger_error(
    'Using CsvManager\Csv is deprecated. Use CsvMananger\Facades\Csv instead.',
    E_USER_DEPRECATED
);

/**
 * Legacy Csv for retro-compatibility with ver. 1.1.0
 */
class Csv extends aliasCsv
{

}