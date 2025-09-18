<?php

namespace CsvManager\Core;

class LanguageManager
{
    const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    const LOCALE_PATH = self::BASE_PATH . 'locales'
        . DIRECTORY_SEPARATOR . '%s.php';

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
        $language = ConfigManager::get('language', 'en');

        $langFile = sprintf(self::LOCALE_PATH, $language);
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