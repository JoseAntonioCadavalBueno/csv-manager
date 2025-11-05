<?php

namespace CsvManager\Core;

class LanguageManager
{
    const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    const LOCALE_PATH = self::BASE_PATH . 'locales'
        . DIRECTORY_SEPARATOR . '%s.php';

    protected ConfigManager $config;
    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
    }

    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

    /**
     * A function that returns the translated message
     *
     * @param string $key
     * @return string
     */
    public function getMessage(string $key): string
    {
        $language = $this->config->get('language', 'en');

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