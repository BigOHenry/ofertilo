<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\InvalidColorException;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public function testCreateEmptyColor(): void
    {
        $color = Color::createEmpty();

        $this->assertNull($color->getId());
        $this->assertFalse($color->isInStock());
        $this->assertTrue($color->isEnabled());
    }

    public function testCreateColorWithCode(): void
    {
        $color = Color::create(3025);
        $this->assertNull($color->getId());
        $this->assertSame(3025, $color->getCode());
        $this->assertFalse($color->isInStock());
        $this->assertTrue($color->isEnabled());
    }

    public function testCreateColorWithInvalidCodeThrowsException(): void
    {
        $this->expectException(InvalidColorException::class);
        $this->expectExceptionMessage('Color code is lower than minimum allowed value 1000');
        Color::create(500);
    }

    public function testCreateColorWithTooHighCodeThrowsException(): void
    {
        $this->expectException(InvalidColorException::class);
        $this->expectExceptionMessage('Color code exceeds maximum allowed value 9999');

        Color::create(10000);
    }

    public function testSetValidCode(): void
    {
        $color = Color::createEmpty();
        $color->setCode(3025);

        $this->assertSame(3025, $color->getCode());
    }

    public function testSetInvalidCodeThrowsException(): void
    {
        $color = Color::createEmpty();

        $this->expectException(InvalidColorException::class);
        $color->setCode(999);
    }

    public function testSetTooHighCodeThrowsException(): void
    {
        $color = Color::createEmpty();

        $this->expectException(InvalidColorException::class);
        $color->setCode(10000);
    }


    public function testMultipleTranslationsForDescription(): void
    {
        $color = Color::createEmpty();
        $color->setDescription('Red color', 'en');
        $color->setDescription('Červená barva', 'cs');

        $this->assertSame('Red color', $color->getDescription('en'));
        $this->assertSame('Červená barva', $color->getDescription('cs'));
    }

    public function testGetDescriptionWithDefaultLocale(): void
    {
        $color = Color::createEmpty();
        $color->setDescription('Default description');

        $this->assertSame('Default description', $color->getDescription());
        $this->assertSame('Default description', $color->getDescription('en'));
    }

    public function testSetTooLongDescriptionThrowsException(): void
    {
        $color = Color::createEmpty();
        $longDescription = str_repeat('a', 101);

        $this->expectException(InvalidColorException::class);
        $this->expectExceptionMessage('Color description must be maximum 100 characters');

        $color->setDescription($longDescription);
    }

    public function testIsEnabledByDefault(): void
    {
        $color = Color::createEmpty();

        $this->assertTrue($color->isEnabled());
    }


    public function testSetEnabled(): void
    {
        $color = Color::createEmpty();
        $color->setEnabled(false);

        $this->assertFalse($color->isEnabled());

        $color->setEnabled(true);
        $this->assertTrue($color->isEnabled());
    }

    public function testIsInStockByDefault(): void
    {
        $color = Color::createEmpty();

        $this->assertFalse($color->isInStock());
    }

    public function testSetInStock(): void
    {
        $color = Color::createEmpty();
        $color->setInStock(true);

        $this->assertTrue($color->isInStock());

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
        $color = Color::create(3025);
        $color->setDescription('Chestnut red', 'en');
        $color->setDescription('Kaštanově červená', 'cs');
        $color->setEnabled(true);
        $color->setInStock(true);

        $this->assertSame(3025, $color->getCode());
        $this->assertSame('Chestnut red', $color->getDescription('en'));
        $this->assertSame('Kaštanově červená', $color->getDescription('cs'));
        $this->assertTrue($color->isEnabled());
        $this->assertTrue($color->isInStock());
    }

    public function testValidCodeBoundaries(): void
    {
        $color1 = Color::create(1000);
        $this->assertSame(1000, $color1->getCode());

        $color2 = Color::create(9999);
        $this->assertSame(9999, $color2->getCode());
    }

    public function testInvalidCodeBoundaries(): void
    {
        $this->expectException(InvalidColorException::class);
        Color::create(999);
    }

    public function testMaximumInvalidCodeBoundary(): void
    {
        $this->expectException(InvalidColorException::class);
        Color::create(10000);
    }

    public function testDescriptionWithExactlyMaxLength(): void
    {
        $color = Color::createEmpty();
        $description = str_repeat('a', 100);

        $color->setDescription($description);
        $this->assertSame($description, $color->getDescription());
    }

    public function testEmptyDescriptionIsAllowed(): void
    {
        $color = Color::createEmpty();
        $color->setDescription('');

        $this->assertSame('', $color->getDescription());
    }

    public function testNullDescriptionIsAllowed(): void
    {
        $color = Color::createEmpty();
        $color->setDescription(null);

        $this->assertNull($color->getDescription());
    }
}
