# 📦 CSV Manager
![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)
![Pipeline Status](https://gitlab.com/jcadavalbueno/csv-manager/badges/main/pipeline.svg)
![Downloads](https://img.shields.io/packagist/dt/atomsk/csv-manager)
![Latest Version](https://img.shields.io/packagist/v/atomsk/csv-manager)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2FJoseAntonioCadavalBueno%2Fcsv-manager.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2FJoseAntonioCadavalBueno%2Fcsv-manager?ref=badge_shield)


PHP library for efficient management of large CSV files. Designed for projects in Laravel, Symfony, or native PHP, with a simple and customizable interface.

> ## ⚠️ Deprecated Features
> 
> Some features and configurations have been deprecated and will be removed in future versions.
> Please update your code accordingly to ensure compatibility.
> 
> ### 1. Allowed_extensions as a string:
> 
> Starting from version 1.3, the `allowed_extensions` variable in the `csv-manager.php` configuration file must now be defined as an array instead of a string.
>
> **old (Deprecated):**
>
> ```php
> 'allowed_extensions' => 'csv,txt'
> ```
> 
> **New (recommended):**
> 
> ```php
> 'allowed_extensions' => ['csv', 'txt']
> ```
> 
> ### 2. Using CsvManager\Csv facade:
> 
> Starting from version 1.1, the namespace of the `Csv` facade has changed.  
>
> **old (Deprecated):**
>
> ```php
> use CsvManager\Csv;
> ```
> 
> **New (recommended):**
>
> ```php
> use CsvManager\Facades\Csv;
> ```
> 
> ### 3. Symfony environment support:
> 
> Starting from the version 1.3, support for the symfony environment has been deprecated.
> This is because the `SymfonyCsv.php` and `NativeCsv.php` integration are very similar and will be merged in future versions.
> 
> **Old (Deprecated):**
>
> ```php
> 'env_config' => 'symfony'
> ```
> 
> **New (recommended):**
> 
> ```php
> 'env_config' => 'native' // or 'laravel' if you prefer
> ```

## 🚀 How to install
You just need to install it like any php library with composer.
```bash
    composer require atomsk/csv-manager
```

## ⚙️ Configuration
The library allows you to customize its behavior through the configuration file `csv-manager.php`.  
You can generate a custom configuration for your environment with:

```bash
    ./vendor/bin/generate-csv-manager-config
```
The file looks like this:
```php
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
```

## 🛠️ Example

The library can be used in two different ways:
Using the Facade (static access) or using the Object-Oriented approach (non-static).

### Using the Facade (recommended for simplicity)

```php
    use CsvManager\Facades\Csv;
    
    $data = Csv::toArray('my-csv-file.csv');
```

```php
    use CsvManager\Facades\Csv;
    
    $data = ['foo', 'poo'];
    
    $myCsvPath = Csv::fromArray($data);
```

For larger files, you can add custom functions that perform any process you want.
```php
    use CsvManager\Facades\Csv;
    
    Csv::toArray('my-csv-file.csv', true, function (array $row)
        {
            // YOUR CODE HERE...
        }
    );
```

### Using the Non-Static (Object-Oriented) approach

If you need more control or want to integrate the library deeply with your own dependency container, you can directly instantiate the integration classes.

**Example with Native environment**

```php
use CsvManager\Core\ConfigManager;
use CsvManager\Core\LanguageManager;
use CsvManager\Integrations\NativeCsv;
use CsvManager\Sources\TrustedFylesystemSource;

// Load configuration (could be from your own array or file)
$config = new ConfigManager([
    'language' => 'en',
    'allowed_extensions' => ['csv', 'txt']
]);

$language   = new LanguageManager($config);
$csv        = new NativeCsv($language);

// Define the source
$source = new TrustedFylesystemSource(
    $config,
    $language,
    __DIR__ . '/storage',
    'example.csv'
);

// Convert array to CSV
$data = [
    ['id' => 1, 'name' => 'John'],
    ['id' => 2, 'name' => 'Jane']
];
$csvPath = $csv->fromArray($data, $source);

echo "CSV created at: " . $csvPath;

// Convert CSV back to array
$result = $csv->toArray($source, true);
print_r($result);
```

**Example with Laravel environment**
```php
use CsvManager\Core\ConfigManager;
use CsvManager\Core\LanguageManager;
use CsvManager\Integrations\LaravelCsv;
use CsvManager\Sources\TrustedFylesystemSource;

// Load configuration (could be from your own array or file)
$config = new ConfigManager([
    'language' => 'en',
    'allowed_extensions' => ['csv', 'txt']
]);

$language   = new LanguageManager($config);
$csv        = new LaravelCsv($language);

// In Laravel, you can specify a disk if needed
$source = new TrustedFylesystemSource($config, $language, storage_path('app/public'), 'my-laravel.csv', 'public');

// Create CSV
$csv->fromArray([['product' => 'Book', 'price' => 12]], $source);
```

**Example with Symfony environment**
> ⚠️ Deprecated since version 1.3
> Symfony environment support will be removed in future versions.
> Prefer using the Native or Laravel environments.

```php
use CsvManager\Core\ConfigManager;
use CsvManager\Core\LanguageManager;
use CsvManager\Integrations\SymfonyCsv;
use CsvManager\Sources\TrustedFylesystemSource;

// Load configuration (could be from your own array or file)
$config = new ConfigManager([
    'language' => 'en',
    'allowed_extensions' => ['csv', 'txt']
]);

$language   = new LanguageManager($config);
$csv        = new SymfonyCsv($language);

$source = new TrustedFylesystemSource($config, $language, __DIR__, 'example.csv');

$csv->fromArray([['foo' => 'bar']], $source);
```

> Works with PHP **8.0**, **8.1**, **8.2**, **8.3** and **8.4**

## 🪪 License
MIT - Open source, free to use and modify.


[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2FJoseAntonioCadavalBueno%2Fcsv-manager.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2FJoseAntonioCadavalBueno%2Fcsv-manager?ref=badge_large)