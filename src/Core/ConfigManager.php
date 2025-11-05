<?php

namespace CsvManager\Core;

class ConfigManager
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get the whole config array
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get a single config value.
     *
     * @param string        $key
     * @param mixed|null    $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->config[$key] ?? $default;
        // For retro-compatibility with ver. 1.2.0
        if ($key === 'allowed_extensions' && is_string($value))
        {
            $value = array_map('trim', explode(',', $value));
        }
        return $value;
    }
}