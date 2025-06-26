<?php

namespace CsvManager\src\Core;

class LanguageManager
{
    const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    const LOCALE_PATH = self::BASE_PATH . 'locales'
        . DIRECTORY_SEPARATOR . '%s.php';

    const DEFAULT_CONFIG_PATH   = self::BASE_PATH . 'config' . DIRECTORY_SEPARATOR . 'csv-manager.php';
    const CUSTOM_CONFIG_PATH    = self::BASE_PATH . '..'
        . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . 'config'
        . DIRECTORY_SEPARATOR . 'csv-manager.php';

    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

    /**
     * A function that returns the translated message
     *
     * @param string $key
     * @return string
     */
    public static function getMessage(string $key): string
    {
        $config = file_exists(self::CUSTOM_CONFIG_PATH)
            ? require self::CUSTOM_CONFIG_PATH
            : require self::DEFAULT_CONFIG_PATH;

        $langFile = sprintf(self::LOCALE_PATH, $config['language']);
        $messages = include $langFile;

        return self::getNestedMessage($messages, $key);
    }

    /* ************************ */
    /* PRIVATE HELPER FUNCTIONS */
    /* ************************ */

    /**
     * Function that chooses the translated message
     *
     * @param array $messages
     * @param string $key
     * @return string|null
     */
    private static function getNestedMessage(array $messages, string $key): ?string
    {
        $keys = explode('.', $key);
        foreach ($keys as $value) {
            if (!isset($messages[$value])) {
                return null;
            }

            $messages = $messages[$value];
        }
        return $messages;
    }
}