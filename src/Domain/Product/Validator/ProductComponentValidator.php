<?php

declare(strict_types=1);

namespace App\Domain\Product\Validator;

use App\Domain\Shared\File\ValueObject\FileType;
use App\Domain\Shared\Validator\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductComponentValidator extends Validator
{
    public const int QUANTITY_MIN_VALUE = 1;
    public const int QUANTITY_MAX_VALUE = 99;
    public const int LENGTH_MIN_VALUE = 1;
    public const int LENGTH_MAX_VALUE = 9999;
    public const int WIDTH_MIN_VALUE = 1;
    public const int WIDTH_MAX_VALUE = 9999;
    public const int THICKNESS_MIN_VALUE = 1;
    public const int THICKNESS_MAX_VALUE = 200;

    /**
     * @return array<string, array{key: string, params?: array<string, float|int|string|null>}>
     */
    public static function validate(int $quantity, int $length, int $width, int $thickness, ?UploadedFile $blueprintFile): array
    {
        return array_merge(
            self::validateIntegerValue('quantity', $quantity, self::QUANTITY_MIN_VALUE, self::QUANTITY_MAX_VALUE),
            self::validateIntegerValue('length', $length, self::LENGTH_MIN_VALUE, self::LENGTH_MAX_VALUE),
            self::validateIntegerValue('width', $width, self::WIDTH_MIN_VALUE, self::WIDTH_MAX_VALUE),
            self::validateIntegerValue('thickness', $thickness, self::THICKNESS_MIN_VALUE, self::THICKNESS_MAX_VALUE),
            self::validateUploadedFile('blueprintFile', $blueprintFile, FileType::IMAGE),
        );
    }
}
