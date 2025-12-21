<?php

declare(strict_types=1);

namespace App\Domain\Shared\Validator;

class Validator
{
    /**
     * @return array{key: string, params: string}
     */
    protected static function validateStringLength(string $field, ?string $string, ?int $min = null, ?int $max = null): array
    {
        $errors = [];
        $strLen = mb_strlen(mb_trim($string));

        if (($min !== null) && $strLen < $min) {
            $errors[$field] = ['key' => 'general.string.tooShort', 'params' => ['%min%' => $min]];
        }

        if (($max !== null) && $strLen > $max) {
            $errors[$field] = ['key' => 'general.string.tooLong', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }

    /**
     * @return array{key: string, params: string}
     */
    protected static function validateIntegerValue(string $field, ?int $integer, ?int $min = null, ?int $max = null): array
    {
        $errors = [];

        if (($min !== null) && $integer < $min) {
            $errors[$field] = ['key' => 'general.int.tooSmall', 'params' => ['%min%' => $min]];
        }

        if (($max !== null) && $integer > $max) {
            $errors[$field] = ['key' => 'general.int.tooBig', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }
}
