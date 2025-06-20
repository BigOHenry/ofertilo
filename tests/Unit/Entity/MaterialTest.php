<?php

namespace App\Tests\Unit\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\ValueObject\Type;
use PHPUnit\Framework\TestCase;

class MaterialTest extends TestCase
{
    public function testConstructor(): void
    {
        $material = new Material();
        $this->assertNull($material->getId());
        $this->assertNull($material->getName());
        $this->assertNull($material->getType());
        $this->assertNull($material->getLatinName());
        $this->assertNull($material->getDryDensity());
        $this->assertNull($material->getHardness());
        $this->assertNull($material->getDescription());
        $this->assertNull($material->getPlaceOfOrigin());
        $this->assertTrue($material->isEnabled());
        $this->assertEmpty($material->getPrices());
    }

    public function testSetAndGetName(): void
    {
        $material = new Material();
        $material->setName('oak');
        $this->assertEquals('oak', $material->getName());
    }

    public function testSetAndGetLatinName(): void
    {
        $material = new Material();
        $material->setLatinName('Quercus');
        $this->assertEquals('Quercus', $material->getLatinName());
    }

    public function testSetAndGetDryDensity(): void
    {
        $material = new Material();
        $material->setDryDensity(500);
        $this->assertEquals(500, $material->getDryDensity());
    }

    public function testSetAndGetHardness(): void
    {
        $material = new Material();
        $material->setHardness(85);
        $this->assertEquals(85, $material->getHardness());
    }

    public function testSetAndGetEnabled(): void
    {
        $material = new Material();
        $material->setEnabled(false);
        $this->assertFalse($material->isEnabled());
    }

    public function testSetAndGetType(): void
    {
        $material = new Material();
        $material->setType(Type::VOLUME);
        $this->assertEquals(Type::VOLUME, $material->getType());
    }

    public function testSetAndGetDescriptionTranslation(): void
    {
        $material = new Material();
        $material->setDescription('Oak', 'en');
        $material->setDescription('Dub', 'cs');
        $this->assertEquals('Oak', $material->getDescription('en'));
        $this->assertEquals('Dub', $material->getDescription('cs'));
    }

    public function testSetAndGetPlaceOfOriginTranslation(): void
    {
        $material = new Material();
        $material->setPlaceOfOrigin('Czech republic', 'en');
        $material->setPlaceOfOrigin('Česká republika', 'cs');
        $this->assertEquals('Czech republic', $material->getPlaceOfOrigin('en'));
        $this->assertEquals('Česká republika', $material->getPlaceOfOrigin('cs'));
    }
}
