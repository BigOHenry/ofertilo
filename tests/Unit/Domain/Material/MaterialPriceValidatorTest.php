<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material;

use App\Domain\Material\Validator\MaterialPriceValidator;
use PHPUnit\Framework\TestCase;

final class MaterialPriceValidatorTest extends TestCase
{
    public function testValidateValidData(): void
    {
        $errors = MaterialPriceValidator::validate(18, 1500.50);

        $this->assertEmpty($errors);
    }

    public function testValidateMinimumValidThickness(): void
    {
        $errors = MaterialPriceValidator::validate(1, 100.00);

        $this->assertEmpty($errors);
    }

    public function testValidateMaximumValidThickness(): void
    {
        $errors = MaterialPriceValidator::validate(100, 1500.00);

        $this->assertEmpty($errors);
    }

    public function testValidateMinimumValidPrice(): void
    {
        $errors = MaterialPriceValidator::validate(18, 1.00);
        $this->assertEmpty($errors);
    }

    public function testValidateThicknessTooLow(): void
    {
        $errors = MaterialPriceValidator::validate(0, 1000.00);

        $this->assertArrayHasKey('thickness', $errors);
    }

    public function testValidateNegativeThickness(): void
    {
        $errors = MaterialPriceValidator::validate(-5, 1000.00);

        $this->assertArrayHasKey('thickness', $errors);
    }

    public function testValidateThicknessTooHigh(): void
    {
        $errors = MaterialPriceValidator::validate(1000, 1000.00);

        $this->assertArrayHasKey('thickness', $errors);
    }

    public function testValidatePriceTooLow(): void
    {
        $errors = MaterialPriceValidator::validate(18, 0.0);

        $this->assertArrayHasKey('price', $errors);
    }

    public function testValidateNegativePrice(): void
    {
        $errors = MaterialPriceValidator::validate(18, -100.00);

        $this->assertArrayHasKey('price', $errors);
    }

    public function testValidatePriceTooHigh(): void
    {
        $errors = MaterialPriceValidator::validate(18, 1000000.01);

        $this->assertArrayHasKey('price', $errors);
    }

    public function testValidateMultipleErrors(): void
    {
        $errors = MaterialPriceValidator::validate(0, -100.00);

        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('thickness', $errors);
        $this->assertArrayHasKey('price', $errors);
    }

    public function testValidateCommonThicknessValues(): void
    {
        $commonThicknesses = [8, 10, 12, 15, 18, 20, 22, 25, 30, 40, 50];

        foreach ($commonThicknesses as $thickness) {
            $errors = MaterialPriceValidator::validate($thickness, 1500.00);
            $this->assertEmpty($errors, "Thickness $thickness should be valid");
        }
    }

    public function testValidateDecimalPrices(): void
    {
        $errors1 = MaterialPriceValidator::validate(18, 1234.56);
        $this->assertEmpty($errors1);

        $errors2 = MaterialPriceValidator::validate(18, 99.99);
        $this->assertEmpty($errors2);

        $errors3 = MaterialPriceValidator::validate(18, 10000.00);
        $this->assertEmpty($errors3);
    }

    public function testValidateBoundaryThickness(): void
    {
        $errors1 = MaterialPriceValidator::validate(1, 100.00);
        $this->assertEmpty($errors1);

        $errors2 = MaterialPriceValidator::validate(100, 100.00);
        $this->assertEmpty($errors2);

        $errors3 = MaterialPriceValidator::validate(0, 100.00);
        $this->assertArrayHasKey('thickness', $errors3);

        $errors4 = MaterialPriceValidator::validate(101, 100.00);
        $this->assertArrayHasKey('thickness', $errors4);
    }

    public function testValidateBoundaryPrice(): void
    {
        $errors1 = MaterialPriceValidator::validate(18, 1.00);
        $this->assertEmpty($errors1);

        $errors2 = MaterialPriceValidator::validate(18, 0.99);
        $this->assertArrayHasKey('price', $errors2);

        $errors3 = MaterialPriceValidator::validate(18, 999999);
        $this->assertEmpty($errors3);

        $errors4 = MaterialPriceValidator::validate(18, 1000000.00);
        $this->assertArrayHasKey('price', $errors4);
    }

    public function testValidateRealWorldScenarios(): void
    {
        // Běžná překližka 18mm
        $errors1 = MaterialPriceValidator::validate(18, 1450.00);
        $this->assertEmpty($errors1);

        // Tenká překližka 4mm
        $errors2 = MaterialPriceValidator::validate(4, 850.50);
        $this->assertEmpty($errors2);

        // Silná překližka 40mm
        $errors3 = MaterialPriceValidator::validate(40, 3500.75);
        $this->assertEmpty($errors3);

        // Masiv 50mm
        $errors4 = MaterialPriceValidator::validate(50, 5200.00);
        $this->assertEmpty($errors4);
    }
}
