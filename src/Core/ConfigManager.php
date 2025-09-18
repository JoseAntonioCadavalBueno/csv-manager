<?php

namespace CsvManager\Core;

class ConfigManager
{
    private static ?array $config = null;

    const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    const DEFAULT_CONFIG_PATH   = self::BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'csv-manager.php';
    const CUSTOM_CONFIG_PATH    = self::BASE_PATH . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'config'
    . DIRECTORY_SEPARATOR . 'csv-manager.php';

    /**
     * Get the whole config array
     *
     * @return array
     */
    public static function getConfig(): array
    {
        if (is_null(self::$config))
        {
            self::$config = file_exists(self::CUSTOM_CONFIG_PATH)
                ? require self::CUSTOM_CONFIG_PATH
                : require self::DEFAULT_CONFIG_PATH;
        }

        return self::$config;
    }

    /**
     * Get a single config value.
     *
     * @param string        $key
     * @param mixed|null    $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getConfig()[$key] ?? $default;
    }
}