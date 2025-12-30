<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Wood;

use App\Domain\Wood\Validator\WoodValidator;
use PHPUnit\Framework\TestCase;

final class WoodValidatorTest extends TestCase
{
    public function testValidateValidData(): void
    {
        $errors = WoodValidator::validate('oak', 'Quercus', 750, 6000);

        $this->assertEmpty($errors);
    }

    public function testValidateInvalidNameFormat(): void
    {
        $errors = WoodValidator::validate('Oak Tree', null, null, null);

        $this->assertArrayHasKey('name', $errors);
        $this->assertSame('wood.name.invalid', $errors['name']['key']);
    }

    public function testValidateNameTooShort(): void
    {
        $errors = WoodValidator::validate('a', null, null, null);

        $this->assertArrayHasKey('name', $errors);
    }

    public function testValidateNameTooLong(): void
    {
        $errors = WoodValidator::validate(str_repeat('a', 256), null, null, null);

        $this->assertArrayHasKey('name', $errors);
    }

    public function testValidateLatinNameTooLong(): void
    {
        $errors = WoodValidator::validate('oak', str_repeat('A', 301), null, null);

        $this->assertArrayHasKey('latinName', $errors);
    }

    public function testValidateDryDensityTooLow(): void
    {
        $errors = WoodValidator::validate('oak', null, -1, null);

        $this->assertArrayHasKey('dryDensity', $errors);
    }

    public function testValidateDryDensityTooHigh(): void
    {
        $errors = WoodValidator::validate('oak', null, 10001, null);

        $this->assertArrayHasKey('dryDensity', $errors);
    }

    public function testValidateHardnessTooLow(): void
    {
        $errors = WoodValidator::validate('oak', null, null, -1);

        $this->assertArrayHasKey('hardness', $errors);
    }

    public function testValidateHardnessTooHigh(): void
    {
        $errors = WoodValidator::validate('oak', null, null, 100001);

        $this->assertArrayHasKey('hardness', $errors);
    }

    public function testValidateMultipleErrors(): void
    {
        $errors = WoodValidator::validate('Oak Tree', str_repeat('A', 301), -1, 100001);

        $this->assertCount(4, $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('latinName', $errors);
        $this->assertArrayHasKey('dryDensity', $errors);
        $this->assertArrayHasKey('hardness', $errors);
    }
}
