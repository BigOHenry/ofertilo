<?php

declare(strict_types=1);

namespace App\Domain\Material\Validator;

use App\Domain\Shared\Validator\Validator;

class MaterialPriceValidator extends Validator
{
    public const int THICKNESS_MIN_VALUE = 1;
    public const int THICKNESS_MAX_VALUE = 20;
    public const int PRICE_MIN_VALUE = 1;
    public const int PRICE_MAX_VALUE = 999999;

    /**
     * @return array<string, array{key: string, params?: array<string, int|float|null>}>
     */
    public static function validate(int $thickness, float $float): array
    {
        return array_merge(
            self::validateThickness($thickness),
            self::validatePrice($float),
        );
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateThickness(int $value): array
    {
        return self::validateIntegerValue('thickness', $value, self::THICKNESS_MIN_VALUE, self::THICKNESS_MAX_VALUE);
    }

    /**
     * @return array<string, array{key: string, params?: array<string, float|null>}>
     */
    protected static function validatePrice(float $value): array
    {
        return self::validateFloatValue('price', $value, self::PRICE_MIN_VALUE, self::PRICE_MAX_VALUE);
    }
}
