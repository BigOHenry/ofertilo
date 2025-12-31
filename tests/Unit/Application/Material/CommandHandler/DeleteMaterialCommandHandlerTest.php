<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Command\Material\DeleteMaterial\DeleteMaterialCommand;
use App\Application\Command\Material\DeleteMaterial\DeleteMaterialCommandHandler;
use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteMaterialCommandHandlerTest extends TestCase
{
    private MaterialApplicationService&MockObject $materialService;
    private DeleteMaterialCommandHandler $handler;

    protected function setUp(): void
    {
        $this->materialService = $this->createMock(MaterialApplicationService::class);
        $this->handler = new DeleteMaterialCommandHandler($this->materialService);
    }

    public function testHandleDeletesMaterialSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $command = DeleteMaterialCommand::create(1);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('delete')
            ->with($material)
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialNotFound(): void
    {
        $command = DeleteMaterialCommand::create(999);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willThrowException(MaterialNotFoundException::withId(999))
        ;

        $this->expectException(MaterialNotFoundException::class);

        $this->handler->__invoke($command);
    }
}
