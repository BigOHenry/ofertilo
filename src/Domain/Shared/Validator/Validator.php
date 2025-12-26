<?php

declare(strict_types=1);

namespace App\Domain\Shared\Validator;

class Validator
{
    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateStringLength(string $field, ?string $string, ?int $min = null, ?int $max = null): array
    {
        $errors = [];
        $strLen = mb_strlen(mb_trim($string ?? ''));

        if (($min !== null) && $strLen < $min) {
            $errors[$field] = ['key' => 'general.string.tooShort', 'params' => ['%min%' => $min]];
        }

        if (($max !== null) && $strLen > $max) {
            $errors[$field] = ['key' => 'general.string.tooLong', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateIntegerValue(string $field, ?int $integer, ?int $min = null, ?int $max = null): array
    {
        $errors = [];

        if (($min !== null) && $integer < $min) {
            $errors[$field] = ['key' => 'general.number.tooSmall', 'params' => ['%min%' => $min]];
        }

        if (($max !== null) && $integer > $max) {
            $errors[$field] = ['key' => 'general.number.tooBig', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, float|null>}>
     */
    protected static function validateFloatValue(string $field, ?float $float, ?float $min = null, ?float $max = null): array
    {
        $errors = [];

        if (($min !== null) && $float < $min) {
            $errors[$field] = ['key' => 'general.number.tooSmall', 'params' => ['%min%' => $min]];
        }

        if (($max !== null) && $float > $max) {
            $errors[$field] = ['key' => 'general.number.tooBig', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }
}
