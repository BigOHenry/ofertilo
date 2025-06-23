<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Shared\Entity\Country;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $country = new Country('Czech Republic', 'cz', 'cze', true);

        $this->assertNull($country->getId());
        $this->assertSame('Czech Republic', $country->getName());
        $this->assertSame('CZ', $country->getAlpha2()); // Automatically converted to uppercase
        $this->assertSame('cze', $country->getAlpha3()); // Automatically converted to uppercase
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

    public function testSetAndGetId(): void
    {
        $country = new Country('France', 'fr', 'fra');
        $country->setId(123);

        $this->assertSame(123, $country->getId());
    }

    public function testSetAndGetName(): void
    {
        $country = new Country('Initial', 'xx', 'xxx');
        $country->setName('Updated Name');

        $this->assertSame('Updated Name', $country->getName());
    }

    public function testSetAndGetNameWithNull(): void
    {
        $country = new Country('Initial', 'xx', 'xxx');
        $country->setName(null);

        $this->assertNull($country->getName());
    }

    public function testSetAndGetAlpha2(): void
    {
        $country = new Country('Test', 'xx', 'xxx');
        $country->setAlpha2('us');

        $this->assertSame('US', $country->getAlpha2());
    }

    public function testSetAndGetAlpha2WithNull(): void
    {
        $country = new Country('Test', 'xx', 'xxx');
        $country->setAlpha2(null);

        $this->assertNull($country->getAlpha2());
    }

    public function testSetAndGetAlpha3(): void
    {
        $country = new Country('Test', 'xx', 'xxx');
        $country->setAlpha3('usa');

        $this->assertSame('USA', $country->getAlpha3());
    }

    public function testSetAndGetAlpha3WithNull(): void
    {
        $country = new Country('Test', 'xx', 'xxx');
        $country->setAlpha3(null);

        $this->assertNull($country->getAlpha3());
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

    public function testCompleteCountryConfiguration(): void
    {
        $country = new Country('Slovakia', 'sk', 'svk', true);
        $country->setId(1);

        $this->assertSame(1, $country->getId());
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

    public function testMultibyteStringHandling(): void
    {
        $country = new Country('Česká republika', 'cz', 'cze');

        $this->assertSame('Česká republika', $country->getName());
        $this->assertSame('CZ', $country->getAlpha2());
        $this->assertSame('CZE', $country->getAlpha3());
    }

    public function testUpdateAllProperties(): void
    {
        $country = new Country('Original', 'xx', 'xxx', false);

        // Update all properties
        $country->setId(999);
        $country->setName('Updated Country');
        $country->setAlpha2('up');
        $country->setAlpha3('upd');
        $country->setEnabled(true);

        // Verify updates
        $this->assertSame(999, $country->getId());
        $this->assertSame('Updated Country', $country->getName());
        $this->assertSame('up', $country->getAlpha2());
        $this->assertSame('upd', $country->getAlpha3());
        $this->assertTrue($country->isEnabled());
    }
}
