<?php

return [

    /*
    | Define the environment config.
    | --------------------------------------------------------------
    | You can define a native, laravel or symfony environment config.
    | Warning: Support for the symfony environment has been deprecated.
    */
    'env_config' => 'native',

    /*
    | Define the language of the messages for the use of the library
    | --------------------------------------------------------------
    |
    */
    'language' => 'en',

    /*
    | Define the allowed extensions for files.
    | --------------------------------------------------------------
    |
    */
    'allowed_extensions'    =>  ['csv','txt'],

    /*
    | Whitelist of allowed paths.
    | --------------------------------------------------------------
    | Includes php temp dir and the repository's base path.
    */
    'extra_allowed_paths'   =>  []
];
