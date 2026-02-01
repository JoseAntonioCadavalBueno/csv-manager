<?php

namespace CsvManager\Traits;

trait CsvValidator
{
    /**
     * Flat and normalize array data.
     *
     * @param array $array
     * @param array $notAllowedChars
     * @return array
     */
    public static function arrayFlattenAndNormalize(array $array, array $notAllowedChars): array
    {
        $result = [];
        foreach ($array as $value)
        {
            if (is_array($value))
            {
                array_push($result, ...self::arrayFlattenAndNormalize($value, $notAllowedChars));
            } else
            {
                $result[] = match ($value) {
                    is_bool($value) => $value ? 'true' : 'false',
                    default => trim(str_replace($notAllowedChars, ' ', stripcslashes($value ?? ''))),
                };
            }
        }

        return $result;
    }

    /* ************************* */
    /* PRIVATE HELPERS FUNCTIONS */
    /* ************************* */

    /**
     * Check if is a valid char for csv.
     *
     * @param string    $char
     * @param array     $notAllowedChars
     * @return bool
     */
    private static function isValidChar(string $char, array $notAllowedChars): bool
    {
        // normalize single quotes to double quotes to always evaluate the byte itself
        // and avoid misinterpretation caused by using single quotes.
        $char = stripcslashes($char);
        return strlen($char) === 1 && ord($char) <= 127 && !in_array($char, $notAllowedChars);
    }
}