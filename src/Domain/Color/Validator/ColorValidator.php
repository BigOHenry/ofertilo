<?php

declare(strict_types=1);

namespace App\Domain\Color\Validator;

use App\Domain\Shared\Validator\Validator;

class ColorValidator extends Validator
{
    public const int CODE_MIN_VALUE = 1000;
    public const int CODE_MAX_VALUE = 9999;

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    public static function validate(int $code): array
    {
        return array_merge(
            self::validateCode($code),
        );
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateCode(int $value): array
    {
        return self::validateIntegerValue('code', $value, self::CODE_MIN_VALUE, self::CODE_MAX_VALUE);
    }
}
