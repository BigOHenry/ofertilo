<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Color;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\InvalidColorException;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    public function testCreateColorWithValidCode(): void
    {
        $color = Color::create(code: 5000);

        $this->assertSame(5000, $color->getCode());
        $this->assertFalse($color->isInStock());
        $this->assertTrue($color->isEnabled());
    }

    public function testCreateColorWithAllParameters(): void
    {
        $color = Color::create(code: 7500, inStock: true, enabled: false);

        $this->assertSame(7500, $color->getCode());
        $this->assertTrue($color->isInStock());
        $this->assertFalse($color->isEnabled());
    }

    public function testCreateColorThrowsExceptionForCodeTooLow(): void
    {
        $this->expectException(InvalidColorException::class);

        Color::create(code: 999);
    }

    public function testCreateColorThrowsExceptionForCodeTooHigh(): void
    {
        $this->expectException(InvalidColorException::class);

        Color::create(code: 10000);
    }

    public function testSetCode(): void
    {
        $color = Color::create(code: 5000);
        $color->setCode(6000);

        $this->assertSame(6000, $color->getCode());
    }

    public function testSetCodeThrowsExceptionForInvalidCode(): void
    {
        $color = Color::create(code: 5000);

        $this->expectException(InvalidColorException::class);

        $color->setCode(999);
    }

    public function testSetInStock(): void
    {
        $color = Color::create(code: 5000);

        $this->assertFalse($color->isInStock());

        $color->setInStock(true);
        $this->assertTrue($color->isInStock());
    }

    public function testSetEnabled(): void
    {
        $color = Color::create(code: 5000);

        $this->assertTrue($color->isEnabled());

        $color->setEnabled(false);
        $this->assertFalse($color->isEnabled());
    }

    public function testSetDescription(): void
    {
        $color = Color::create(code: 5000);

        $color->setDescription('Red color', 'en');
        $color->setDescription('Červená barva', 'cs');

        $this->assertSame('Red color', $color->getDescription('en'));
        $this->assertSame('Červená barva', $color->getDescription('cs'));
    }

    public function testSetDescriptionThrowsExceptionWhenTooLong(): void
    {
        $color = Color::create(code: 5000);

        $this->expectException(InvalidColorException::class);

        $color->setDescription(str_repeat('A', 101), 'en');
    }

    public function testGetCodeThrowsExceptionWhenNotInitialized(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Color code is not initialized');

        $reflection = new \ReflectionClass(Color::class);
        $color = $reflection->newInstanceWithoutConstructor();
        $color->getCode();
    }

    public function testFluentInterface(): void
    {
        $color = Color::create(code: 5000);

        $result = $color
            ->setCode(6000)
            ->setInStock(true)
            ->setEnabled(false)
            ->setDescription('Blue color', 'en')
        ;

        $this->assertSame($color, $result);
        $this->assertSame(6000, $color->getCode());
        $this->assertTrue($color->isInStock());
        $this->assertFalse($color->isEnabled());
        $this->assertSame('Blue color', $color->getDescription('en'));
    }
}
