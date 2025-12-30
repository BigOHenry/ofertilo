<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Color;

use App\Domain\Color\Validator\ColorValidator;
use PHPUnit\Framework\TestCase;

final class ColorValidatorTest extends TestCase
{
    public function testValidateValidCode(): void
    {
        $errors = ColorValidator::validate(5000);

        $this->assertEmpty($errors);
    }

    public function testValidateMinimumValidCode(): void
    {
        $errors = ColorValidator::validate(1000);

        $this->assertEmpty($errors);
    }

    public function testValidateMaximumValidCode(): void
    {
        $errors = ColorValidator::validate(9999);

        $this->assertEmpty($errors);
    }

    public function testValidateCodeTooLow(): void
    {
        $errors = ColorValidator::validate(999);

        $this->assertArrayHasKey('code', $errors);
    }

    public function testValidateCodeTooHigh(): void
    {
        $errors = ColorValidator::validate(10000);

        $this->assertArrayHasKey('code', $errors);
    }

    public function testValidateCodeZero(): void
    {
        $errors = ColorValidator::validate(0);

        $this->assertArrayHasKey('code', $errors);
    }

    public function testValidateNegativeCode(): void
    {
        $errors = ColorValidator::validate(-100);

        $this->assertArrayHasKey('code', $errors);
    }
}
