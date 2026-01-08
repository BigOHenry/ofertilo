<?php

declare(strict_types=1);

namespace App\Domain\Product\Validator;

use App\Domain\Shared\Validator\Validator;

class ProductVariantValidator extends Validator
{
    public const int HEIGHT_MIN_VALUE = 1;
    public const int HEIGHT_MAX_VALUE = 9999;
    public const int WIDTH_MIN_VALUE = 1;
    public const int WIDTH_MAX_VALUE = 9999;
    public const null THICKNESS_MIN_VALUE = null;
    public const int THICKNESS_MAX_VALUE = 200;

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    public static function validate(int $height, int $width, ?int $thickness): array
    {
        return array_merge(
            self::validateIntegerValue('height', $height, self::HEIGHT_MIN_VALUE, self::HEIGHT_MAX_VALUE),
            self::validateIntegerValue('width', $width, self::WIDTH_MIN_VALUE, self::WIDTH_MAX_VALUE),
            self::validateIntegerValue('thickness', $thickness, self::THICKNESS_MIN_VALUE, self::THICKNESS_MAX_VALUE),
        );
    }
}
