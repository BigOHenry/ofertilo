<?php

declare(strict_types=1);

namespace App\Domain\Wood\Validator;

use App\Domain\Shared\Validator\Validator;

class WoodValidator extends Validator
{
    public const int NAME_MIN_LENGTH = 3;
    public const int NAME_MAX_LENGTH = 50;
    public const null LATIN_NAME_MIN_LENGTH = null;
    public const int LATIN_NAME_MAX_LENGTH = 300;
    public const int DRY_DENSITY_MIN_VALUE = 1;
    public const int DRY_DENSITY_MAX_VALUE = 5000;
    public const int HARDNESS_MIN_VALUE = 1;
    public const int HARDNESS_MAX_VALUE = 9999;

    /**
     * @return array<string, array{key: string, params: string}>
     */
    public static function validateName(string $name): array
    {
        return self::validateStringLength('name', $name, self::NAME_MIN_LENGTH, self::NAME_MAX_LENGTH);
    }

    /**
     * @return array<string, array{key: string, params: string}>
     */
    public static function validateLatinName(string $latinName): array
    {
        return self::validateStringLength('latinName', $latinName, self::LATIN_NAME_MIN_LENGTH, self::LATIN_NAME_MAX_LENGTH);
    }

    /**
     * @return array<string, array{key: string, params: string}>
     */
    public static function validateDryDensity(?int $dryDensity): array
    {
        return self::validateIntegerValue('dryDensity', $dryDensity, self::DRY_DENSITY_MIN_VALUE, self::DRY_DENSITY_MAX_VALUE);
    }

    /**
     * @return array<string, array{key: string, params: string}>
     */
    public static function validateHardness(?int $hardness): array
    {
        return self::validateIntegerValue('hardness', $hardness, self::HARDNESS_MIN_VALUE, self::HARDNESS_MAX_VALUE);
    }

    /**
     * @return array<string, array{key: string, params?: string}>
     */
    public static function validateForCreation(string $name, ?string $latinName, ?int $dryDensity, ?int $hardness): array
    {
        return array_merge(
            self::validateName($name),
            self::validateLatinName($latinName),
            self::validateDryDensity($dryDensity),
            self::validateHardness($hardness),
        );
    }

    /**
     * @return array<string, array{key: string, params?: string}>
     */
    public static function validateForUpdate(string $name, ?string $latinName, ?int $dryDensity, ?int $hardness): array
    {
        return array_merge(
            self::validateName($name),
            self::validateLatinName($latinName),
            self::validateDryDensity($dryDensity),
            self::validateHardness($hardness),
        );
    }
}
