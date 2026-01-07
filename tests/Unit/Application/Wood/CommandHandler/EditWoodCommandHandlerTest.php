<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Wood\CommandHandler;

use App\Application\Wood\Command\EditWood\EditWoodCommand;
use App\Application\Wood\Command\EditWood\EditWoodCommandHandler;
use App\Application\Wood\Service\WoodApplicationService;
use App\Domain\Translation\DTO\TranslationDto;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use App\Domain\Wood\Exception\WoodValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class EditWoodCommandHandlerTest extends TestCase
{
    private WoodApplicationService&MockObject $woodApplicationService;
    private EditWoodCommandHandler $handler;

    protected function setUp(): void
    {
        $this->woodApplicationService = $this->createMock(WoodApplicationService::class);
        $this->handler = new EditWoodCommandHandler($this->woodApplicationService);
    }

    public function testHandleUpdatesWoodSuccessfully(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);
        $woodId = $wood->getId();

        $command = new EditWoodCommand(
            id: $woodId,
            name: 'oak',
            latinName: 'Quercus robur',
            dryDensity: 800,
            hardness: 6500,
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
            ->with($wood)
        ;

        $this->handler->__invoke($command);

        $this->assertSame('Quercus robur', $wood->getLatinName());
        $this->assertSame(800, $wood->getDryDensity());
        $this->assertSame(6500, $wood->getHardness());
    }

    public function testHandleThrowsExceptionWhenWoodNotFound(): void
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000999';

        $command = new EditWoodCommand(
            id: $nonExistentId,
            name: 'oak',
            latinName: null,
            dryDensity: 750,  // Použití platné hodnoty místo null
            hardness: 6000,   // Použití platné hodnoty místo null
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentId)
            ->willThrowException(WoodNotFoundException::withId($nonExistentId))
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationException(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = new EditWoodCommand(
            id: $woodId,
            name: 'oak',
            latinName: null,
            dryDensity: -100,  // Invalid negative value
            hardness: 6000,    // Valid value
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->willReturn($wood)
        ;

        $this->expectException(WoodValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleUpdatesOnlyProvidedFields(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);
        $woodId = $wood->getId();

        // Command with only latinName updated, keep existing density and hardness
        $command = new EditWoodCommand(
            id: $woodId,
            name: 'oak',
            latinName: 'Quercus updated',
            dryDensity: 750,  // Keep same value
            hardness: 6000,   // Keep same value
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
            ->with($wood)
        ;

        $this->handler->__invoke($command);

        // Updated field
        $this->assertSame('Quercus updated', $wood->getLatinName());

        // Unchanged fields
        $this->assertSame(750, $wood->getDryDensity());
        $this->assertSame(6000, $wood->getHardness());
    }

    public function testHandleUpdatesEnabledStatus(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);
        $woodId = $wood->getId();

        $command = new EditWoodCommand(
            id: $woodId,
            name: 'oak',
            latinName: null,
            dryDensity: 750,  // Keep same value
            hardness: 6000,   // Keep same value
            enabled: false,   // Disable wood
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
            ->with($wood)
        ;

        $this->handler->__invoke($command);

        $this->assertFalse($wood->isEnabled());
        $this->assertSame(750, $wood->getDryDensity());
        $this->assertSame(6000, $wood->getHardness());
    }

    public function testHandleUpdatesTranslations(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);
        $woodId = $wood->getId();

        $translations = [
            TranslationDto::create(
                id: Uuid::uuid4()->toString(),
                objectId: $woodId,
                objectClass: Wood::class,
                locale: 'en',
                field: 'description',
                value: 'Oak wood'
            ),
            TranslationDto::create(
                id: Uuid::uuid4()->toString(),
                objectId: $woodId,
                objectClass: Wood::class,
                locale: 'cs',
                field: 'description',
                value: 'Dubové dřevo'
            ),
        ];

        $command = new EditWoodCommand(
            id: $woodId,
            name: 'oak',
            latinName: null,
            dryDensity: 750,  // Keep same value
            hardness: 6000,   // Keep same value
            enabled: true,
            translations: $translations
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
            ->with($wood)
        ;

        $this->handler->__invoke($command);

        $this->assertSame('Oak wood', $wood->getDescription('en'));
        $this->assertSame('Dubové dřevo', $wood->getDescription('cs'));
    }

    public function testHandleUpdatesDryDensityOnly(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);
        $woodId = $wood->getId();

        $command = new EditWoodCommand(
            id: $woodId,
            name: 'oak',
            latinName: 'Quercus',
            dryDensity: 800,  // Update density
            hardness: 6000,   // Keep same
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertSame(800, $wood->getDryDensity());
        $this->assertSame(6000, $wood->getHardness());
    }
}
