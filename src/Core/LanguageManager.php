<?php

namespace src\Core;

class LanguageManager
{
    const LOCALE_PATH       = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'locales' . DIRECTORY_SEPARATOR . '%s.php';
    const DEFAULT_LANGUAGE  = 'en';

    private static string $languageCode = 'en';

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
        // Set the language.
        $langFile = sprintf(self::LOCALE_PATH, self::$languageCode);

        // If the locale file doesn't exist set the default locale.
        if (!file_exists($langFile)) {
            $langFile = sprintf(self::LOCALE_PATH, self::DEFAULT_LANGUAGE);
        }

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