<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Color;

use App\Domain\Color\Entity\Color;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    public function testCreateColor(): void
    {
        $color = Color::create(5000);

        $this->assertSame(5000, $color->getCode());
        $this->assertFalse($color->isInStock());
        $this->assertTrue($color->isEnabled());
    }

    public function testCreateColorWithCustomValues(): void
    {
        $color = Color::create(6000, true, false);

        $this->assertSame(6000, $color->getCode());
        $this->assertTrue($color->isInStock());
        $this->assertFalse($color->isEnabled());
    }

    public function testSetCode(): void
    {
        $color = Color::create(5000);
        $color->setCode(6000);

        $this->assertSame(6000, $color->getCode());
    }

    public function testSetInStock(): void
    {
        $color = Color::create(5000);

        $this->assertFalse($color->isInStock());

        $color->setInStock(true);

        $this->assertTrue($color->isInStock());
    }

    public function testSetEnabled(): void
    {
        $color = Color::create(5000);

        $this->assertTrue($color->isEnabled());

        $color->setEnabled(false);

        $this->assertFalse($color->isEnabled());
    }

    public function testSetDescription(): void
    {
        $color = Color::create(5000);

        $color->setDescription('Beautiful red', 'en');

        $this->assertSame('Beautiful red', $color->getDescription('en'));
    }

    public function testGetDescriptionReturnsNullForMissingLocale(): void
    {
        $color = Color::create(5000);

        $this->assertNull($color->getDescription('cs'));
    }

    public function testFluentInterface(): void
    {
        $color = Color::create(5000);

        $result = $color
            ->setCode(6000)
            ->setInStock(true)
            ->setEnabled(false)
            ->setDescription('Red', 'en')
        ;

        $this->assertSame($color, $result);
        $this->assertSame(6000, $color->getCode());
        $this->assertTrue($color->isInStock());
        $this->assertFalse($color->isEnabled());
    }
}
