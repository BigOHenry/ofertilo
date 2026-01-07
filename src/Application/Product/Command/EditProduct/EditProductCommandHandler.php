<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProduct;

use App\Application\Product\Service\ProductApplicationService;
use App\Application\Shared\Country\Service\CountryService;
use App\Domain\Product\Exception\ProductAlreadyExistsException;
use App\Domain\Shared\File\Entity\File;
use App\Domain\Shared\File\ValueObject\FileType;
use App\Infrastructure\Service\FileStorage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
        private CountryService $countryService,
        private FileStorage $fileStorage
    ) {
    }

    public function __invoke(EditProductCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->getId());

        $country = null;
        if ($command->getCountryId() !== null) {
            $country = $this->countryService->getEnabledCountryById($command->getCountryId());

            if ($product->getCountry() !== $country) {
                $foundProduct = $this->productApplicationService->findByTypeAndCountry($product->getType(), $country);
                if ($foundProduct !== null && $foundProduct->getId() !== $command->getId()) {
                    throw ProductAlreadyExistsException::withTypeAndCountry($product->getType(), $country);
                }
            }
        }

        $product->setCountry($country);
        $product->setEnabled($command->isEnabled());

        if ($command->getImageFile()) {
            $oldImageFile = $product->getImageFile();

            if ($oldImageFile && $this->fileStorage->exists($oldImageFile)) {
                $this->fileStorage->delete($oldImageFile);
            }

            $newImageFile = File::createFromUploadedFile(
                uploadedFile: $command->getImageFile(),
                type: FileType::IMAGE
            );

            $product->setImageFile($newImageFile);
        }

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $product->addOrUpdateTranslation($translation->getField(), $value, $translation->getLocale());
        }

        $this->productApplicationService->save($product);
    }
}
