# 📦 CSV Manager
PHP library for efficient management of large CSV files. Designed for projects in Laravel, Symfony, or native PHP, with a simple and customizable interface.

## 🚀 How to install
You just need to install it like any php library with composer.
```bash
    composer require atomsk/csv-manager
```

## ⚙️ Configuration
You can generate a customized file depending on the environment.

### 🐘 PHP
You just need to run the following command:
```bash
    ./vendor/bin/generate-config
```

## 🛠️ Example
A basic use example for the library would be something like the following:

```php
    use CsvManager\src\Csv;
    
    $data = Csv::toArray('my-csv-file.csv');
```

```php
    use CsvManager\src\Csv;
    
    $data = ['foo', 'poo'];
    
    $myCsvPath = Csv::fromArray($data);
```

For larger files, you can add custom functions that perform any process you want.
```php
    use CsvManager\src\Csv;
    
    Csv::toArray('my-csv-file.csv', true, function (array $row)
        {
            // YOUR CODE HERE...
        }
    );
```

> Works with PHP 8.0.*, 8.1.*, 8.2.*, 8.3.* and 8.4.*

## 🪪 License
MIT - Open source, free to use and modify.
