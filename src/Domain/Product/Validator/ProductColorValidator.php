<?php

declare(strict_types=1);

namespace App\Domain\Product\Validator;

use App\Domain\Shared\Validator\Validator;

class ProductColorValidator extends Validator
{
    public const null DESCRIPTION_MIN_LENGTH = null;
    public const int DESCRIPTION_MAX_LENGTH = 500;

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    public static function validate(?string $description): array
    {
        return array_merge(
            self::validateDescription($description),
        );
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateDescription(?string $description): array
    {
        return self::validateStringLength('description', $description, self::DESCRIPTION_MIN_LENGTH, self::DESCRIPTION_MAX_LENGTH);
    }
}
