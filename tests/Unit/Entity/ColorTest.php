<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Color\Entity\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public function testConstructor(): void
    {
        $color = new Color();

        $this->assertNull($color->getId());
        $this->assertNull($color->getCode());
        $this->assertFalse($color->isInStock());
        $this->assertTrue($color->isEnabled());
    }

    public function testSetAndGetId(): void
    {
        $color = new Color();
        $color->setId(123);

        $this->assertSame(123, $color->getId());
    }

    public function testSetAndGetCode(): void
    {
        $color = new Color();
        $color->setCode(255);

        $this->assertSame(255, $color->getCode());
    }

    public function testSetAndGetCodeWithZero(): void
    {
        $color = new Color();
        $color->setCode(0);

        $this->assertSame(0, $color->getCode());
    }

    public function testSetAndGetCodeWithNull(): void
    {
        $color = new Color();
        $color->setCode(null);

        $this->assertNull($color->getCode());
    }

    public function testSetAndGetDescription(): void
    {
        $color = new Color();
        $color->setDescription('Red color');

        $this->assertSame('Red color', $color->getDescription());
    }

    public function testSetAndGetDescriptionWithLocale(): void
    {
        $color = new Color();
        $color->setDescription('Red color', 'en');

        $this->assertSame('Red color', $color->getDescription('en'));
    }

    public function testMultipleTranslationsForDescription(): void
    {
        $color = new Color();
        $color->setDescription('Red color', 'en');
        $color->setDescription('Červená barva', 'cs');

        $this->assertSame('Red color', $color->getDescription('en'));
        $this->assertSame('Červená barva', $color->getDescription('cs'));
    }

    public function testGetDescriptionWithDefaultLocale(): void
    {
        $color = new Color();
        $color->setDescription('Default description');

        $this->assertSame('Default description', $color->getDescription());
        $this->assertSame('Default description', $color->getDescription('en'));
    }

    public function testGetDescriptionWithNullLocale(): void
    {
        $color = new Color();
        $color->setDescription('English description', 'en');
        $this->assertSame('English description', $color->getDescription(null));
    }

    public function testIsEnabledByDefault(): void
    {
        $color = new Color();

        $this->assertTrue($color->isEnabled());
    }

    public function testSetEnabled(): void
    {
        $color = new Color();
        $color->setEnabled(false);

        $this->assertFalse($color->isEnabled());
    }

    public function testSetEnabledToTrue(): void
    {
        $color = new Color();
        $color->setEnabled(false);
        $color->setEnabled(true);

        $this->assertTrue($color->isEnabled());
    }

    public function testIsInStockByDefault(): void
    {
        $color = new Color();

        $this->assertFalse($color->isInStock());
    }

    public function testSetInStock(): void
    {
        $color = new Color();
        $color->setInStock(true);

        $this->assertTrue($color->isInStock());
    }

    public function testSetInStockToFalse(): void
    {
        $color = new Color();
        $color->setInStock(true);
        $color->setInStock(false);

        $this->assertFalse($color->isInStock());
    }

    public function testGetTranslatableFields(): void
    {
        $fields = Color::getTranslatableFields();

        $this->assertIsArray($fields);
        $this->assertContains('description', $fields);
        $this->assertCount(1, $fields);
    }

    public function testCompleteColorConfiguration(): void
    {
        $color = new Color();
        $color->setId(1);
        $color->setCode(3025);
        $color->setDescription('Chestnut red', 'en');
        $color->setDescription('Kaštanově červená', 'cs');
        $color->setEnabled(true);
        $color->setInStock(true);

        $this->assertSame(1, $color->getId());
        $this->assertSame(3025, $color->getCode());
        $this->assertSame('Chestnut red', $color->getDescription('en'));
        $this->assertSame('Kaštanově červená', $color->getDescription('cs'));
        $this->assertTrue($color->isEnabled());
        $this->assertTrue($color->isInStock());
    }

    public function testColorWithNegativeCode(): void
    {
        $color = new Color();
        $color->setCode(-1);

        $this->assertSame(-1, $color->getCode());
    }

    public function testColorWithLargeCode(): void
    {
        $color = new Color();
        $color->setCode(16777215); // Maximum RGB value (white)

        $this->assertSame(16777215, $color->getCode());
    }

    public function testToggleInStock(): void
    {
        $color = new Color();

        $this->assertFalse($color->isInStock());

        $color->setInStock(true);
        $this->assertTrue($color->isInStock());

        $color->setInStock(false);
        $this->assertFalse($color->isInStock());
    }

    public function testToggleEnabled(): void
    {
        $color = new Color();

        $this->assertTrue($color->isEnabled());

        $color->setEnabled(false);
        $this->assertFalse($color->isEnabled());

        $color->setEnabled(true);
        $this->assertTrue($color->isEnabled());
    }
}
