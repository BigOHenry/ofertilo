<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Wood;

use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\TestCase;

final class WoodTest extends TestCase
{
    public function testCreateWoodWithAllParameters(): void
    {
        $wood = Wood::create(
            name: 'oak',
            latin_name: 'Quercus',
            dryDensity: 750,
            hardness: 6000
        );

        $this->assertSame('oak', $wood->getName());
        $this->assertSame('Quercus', $wood->getLatinName());
        $this->assertSame(750, $wood->getDryDensity());
        $this->assertSame(6000, $wood->getHardness());
        $this->assertTrue($wood->isEnabled());
    }

    public function testCreateWoodWithMinimalParameters(): void
    {
        $wood = Wood::create(name: 'pine');

        $this->assertSame('pine', $wood->getName());
        $this->assertNull($wood->getLatinName());
        $this->assertNull($wood->getDryDensity());
        $this->assertNull($wood->getHardness());
        $this->assertTrue($wood->isEnabled());
    }

    public function testSetters(): void
    {
        $wood = Wood::create(name: 'ash');

        $wood->setName('maple')
             ->setLatinName('Acer')
             ->setDryDensity(700)
             ->setHardness(5500)
             ->setEnabled(false)
        ;

        $this->assertSame('maple', $wood->getName());
        $this->assertSame('Acer', $wood->getLatinName());
        $this->assertSame(700, $wood->getDryDensity());
        $this->assertSame(5500, $wood->getHardness());
        $this->assertFalse($wood->isEnabled());
    }

    public function testTranslationsDescription(): void
    {
        $wood = Wood::create(name: 'beech');
        $wood->setDescription('Beautiful hardwood', 'en');
        $wood->setDescription('Krásné tvrdé dřevo', 'cs');

        $this->assertSame('Beautiful hardwood', $wood->getDescription('en'));
        $this->assertSame('Krásné tvrdé dřevo', $wood->getDescription('cs'));
    }

    public function testTranslationsPlaceOfOrigin(): void
    {
        $wood = Wood::create(name: 'mahogany');
        $wood->setPlaceOfOrigin('Central America', 'en');
        $wood->setPlaceOfOrigin('Střední Amerika', 'cs');

        $this->assertSame('Central America', $wood->getPlaceOfOrigin('en'));
        $this->assertSame('Střední Amerika', $wood->getPlaceOfOrigin('cs'));
    }

    public function testGetNameThrowsExceptionWhenNotInitialized(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Material name is not initialized');

        $reflection = new \ReflectionClass(Wood::class);
        $wood = $reflection->newInstanceWithoutConstructor();
        $wood->getName();
    }
}
