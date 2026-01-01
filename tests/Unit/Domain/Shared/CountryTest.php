<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared;

use App\Domain\Shared\Country\Entity\Country;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $country = new Country('Czech Republic', 'cz', 'cze', true);

        $this->assertSame('Czech Republic', $country->getName());
        $this->assertSame('CZ', $country->getAlpha2()); // Automatically converted to uppercase
        $this->assertSame('CZE', $country->getAlpha3()); // Automatically converted to uppercase
        $this->assertTrue($country->isEnabled());
    }

    public function testConstructorWithDefaultEnabled(): void
    {
        $country = new Country('Germany', 'de', 'deu');

        $this->assertSame('Germany', $country->getName());
        $this->assertSame('DE', $country->getAlpha2());
        $this->assertSame('DEU', $country->getAlpha3());
        $this->assertTrue($country->isEnabled()); // Default true
    }

    public function testConstructorWithDisabled(): void
    {
        $country = new Country('Test Country', 'xx', 'xxx', false);

        $this->assertSame('Test Country', $country->getName());
        $this->assertSame('XX', $country->getAlpha2());
        $this->assertSame('XXX', $country->getAlpha3());
        $this->assertFalse($country->isEnabled());
    }

    public function testGetName(): void
    {
        $country = new Country('United Kingdom', 'gb', 'gbr');

        $this->assertSame('United Kingdom', $country->getName());
    }

    public function testGetAlpha2(): void
    {
        $country = new Country('United States', 'us', 'usa');

        $this->assertSame('US', $country->getAlpha2());
    }

    public function testGetAlpha3(): void
    {
        $country = new Country('Canada', 'ca', 'can');

        $this->assertSame('CAN', $country->getAlpha3());
    }

    public function testIsEnabledByDefault(): void
    {
        $country = new Country('Test', 'xx', 'xxx');

        $this->assertTrue($country->isEnabled());
    }

    public function testSetEnabled(): void
    {
        $country = new Country('Test', 'xx', 'xxx');
        $country->setEnabled(false);

        $this->assertFalse($country->isEnabled());
    }

    public function testSetEnabledToTrue(): void
    {
        $country = new Country('Test', 'xx', 'xxx', false);
        $country->setEnabled(true);

        $this->assertTrue($country->isEnabled());
    }

    public function testAlpha2CaseConversionInConstructor(): void
    {
        $country = new Country('Test', 'us', 'usa');

        $this->assertSame('US', $country->getAlpha2());
    }

    public function testAlpha3CaseConversionInConstructor(): void
    {
        $country = new Country('Test', 'us', 'usa');

        $this->assertSame('USA', $country->getAlpha3());
    }

    public function testMixedCaseConversionInConstructor(): void
    {
        $country = new Country('Test', 'uS', 'UsA');

        $this->assertSame('US', $country->getAlpha2());
        $this->assertSame('USA', $country->getAlpha3());
    }

    public function testMultibyteStringConversion(): void
    {
        // Test with multibyte characters - mb_strtoupper handles special characters correctly
        $country = new Country('Test', 'ü', 'üöä');

        $this->assertSame('Ü', $country->getAlpha2());
        $this->assertSame('ÜÖÄ', $country->getAlpha3());
    }

    public function testRealWorldCountries(): void
    {
        $czechRepublic = new Country('Czech Republic', 'cz', 'cze');
        $this->assertSame('CZ', $czechRepublic->getAlpha2());
        $this->assertSame('CZE', $czechRepublic->getAlpha3());

        $unitedStates = new Country('United States', 'us', 'usa');
        $this->assertSame('US', $unitedStates->getAlpha2());
        $this->assertSame('USA', $unitedStates->getAlpha3());

        $germany = new Country('Germany', 'de', 'deu');
        $this->assertSame('DE', $germany->getAlpha2());
        $this->assertSame('DEU', $germany->getAlpha3());
    }

    public function testCompleteCountryConfiguration(): void
    {
        $country = new Country('Slovakia', 'sk', 'svk', true);

        // Ověření všech vlastností
        $this->assertSame('Slovakia', $country->getName());
        $this->assertSame('SK', $country->getAlpha2());
        $this->assertSame('SVK', $country->getAlpha3());
        $this->assertTrue($country->isEnabled());
    }

    public function testToggleEnabled(): void
    {
        $country = new Country('Test', 'xx', 'xxx');

        $this->assertTrue($country->isEnabled());

        $country->setEnabled(false);
        $this->assertFalse($country->isEnabled());

        $country->setEnabled(true);
        $this->assertTrue($country->isEnabled());
    }

    public function testEmptyStringHandling(): void
    {
        $country = new Country('', '', '');

        $this->assertSame('', $country->getName());
        $this->assertSame('', $country->getAlpha2());
        $this->assertSame('', $country->getAlpha3());
    }

    public function testSpecialCharactersInName(): void
    {
        $country = new Country('Côte d\'Ivoire', 'ci', 'civ');

        $this->assertSame('Côte d\'Ivoire', $country->getName());
        $this->assertSame('CI', $country->getAlpha2());
        $this->assertSame('CIV', $country->getAlpha3());
    }

    public function testCzechCharactersInName(): void
    {
        // Test s českými znaky v názvu
        $country = new Country('Česká republika', 'cz', 'cze');

        $this->assertSame('Česká republika', $country->getName());
        $this->assertSame('CZ', $country->getAlpha2());
        $this->assertSame('CZE', $country->getAlpha3());
    }

    public function testLowercaseInputConversion(): void
    {
        $country = new Country('Test Country', 'lowercase', 'lowercase');

        $this->assertSame('LOWERCASE', $country->getAlpha2());
        $this->assertSame('LOWERCASE', $country->getAlpha3());
    }

    public function testMbStrToUpperFunctionality(): void
    {
        // Test specifically for mb_strtoupper function
        $country = new Country('Test', 'àáâãäå', 'èéêë');

        $this->assertSame('ÀÁÂÃÄÅ', $country->getAlpha2());
        $this->assertSame('ÈÉÊË', $country->getAlpha3());
    }
}
