# 📦 CSV Manager
![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)
![Pipeline Status](https://gitlab.com/jcadavalbueno/csv-manager/badges/main/pipeline.svg)
![Downloads](https://img.shields.io/packagist/dt/atomsk/csv-manager)
![Latest Version](https://img.shields.io/packagist/v/atomsk/csv-manager)


PHP library for efficient management of large CSV files. Designed for projects in Laravel, Symfony, or native PHP, with a simple and customizable interface.

> ⚠️ **Important notice:**  
> Starting from version 1.1, the namespace of the `Csv` facade has changed.  
> You should now use:
>
> ```php
> use CsvManager\Facades\Csv;
> ```
>
> instead of the old:
>
> ```php
> use CsvManager\Csv;
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
    'allowed_extensions' => 'csv,txt'
];
```

## 🛠️ Example
A basic use example for the library would be something like the following:

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

> Works with PHP 8.0.*, 8.1.*, 8.2.*, 8.3.* and 8.4.*

## 🪪 License
MIT - Open source, free to use and modify.
