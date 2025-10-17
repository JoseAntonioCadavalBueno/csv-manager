<?php

return [
    'errors'    => [
        'illegal_env'       => 'The environment config is not allowed.',
        'not_found'         => 'The file does not exist or is not accessible.',
        'not_found_2'       => 'The directory does not exist or is not accessible.',
        'corrupt'           => 'The file cannot be read correctly or is not properly formatted.',
        'corrupt_2'         => 'File extension not allowed.',
        'overflow'          => 'The csv file is too large.',
        'native_logic'      => 'With the native php configuration the path must be complete in $filename.',
        'symfony_logic'     => 'With the Symfony php configuration the disk cannot be accept.',
        'same_csv_chars'    => "The 'delimiter', 'enclosure', and 'escape' must be different.",
        'invalid_csv_Char'  => "The '%s' character is not allowed."
    ],
    'messages'  => []
];
