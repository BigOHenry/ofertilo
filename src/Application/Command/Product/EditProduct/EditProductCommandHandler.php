<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProduct;

use App\Application\Service\CountryService;
use App\Application\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductAlreadyExistsException;
use App\Domain\Product\Exception\ProductNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
        private CountryService $countryService,
    ) {
    }

    public function __invoke(EditProductCommand $command): void
    {
        $product = $this->productApplicationService->findById($command->getId());

        if ($product === null) {
            throw ProductNotFoundException::withId($command->getId());
        }

        $country = $this->countryService->getEnabledCountryById($command->getCountryId());
        $type = $command->getType();

        if ($product->getType() !== $type || $product->getCountry() !== $country) {
            $foundProduct = $this->productApplicationService->findByTypeAndCountry($type, $country);
            if ($foundProduct !== null && $foundProduct->getId() !== $command->getId()) {
                throw ProductAlreadyExistsException::withTypeAndCountry($type, $country);
            }
        }

        $product->setType($command->getType());
        $product->setCountry($country);
        $product->setEnabled($command->isEnabled());

        if ($command->getImageFile()) {
            $oldImageFilename = $product->getImageFilename();
            if ($oldImageFilename) {
                $this->productApplicationService->removeProductImage($product, $oldImageFilename);
            }
            $this->productApplicationService->handleImageUpload($product, $command->getImageFile());
        }

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $product->addOrUpdateTranslation($translation->getField(), $value, $translation->getLocale());
        }

        $this->productApplicationService->save($product);
    }
}
