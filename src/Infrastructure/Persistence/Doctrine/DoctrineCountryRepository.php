<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Shared\Entity\Country;
use App\Domain\Shared\Repository\CountryRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @phpstan-extends ServiceEntityRepository<\App\Domain\Shared\Entity\Country>
 */
class DoctrineCountryRepository extends ServiceEntityRepository implements CountryRepositoryInterface
{
    private const int CACHE_TTL = 86400; // 24 hours

    public function __construct(
        ManagerRegistry $registry,
        private readonly CacheInterface $cache,
    ) {
        parent::__construct($registry, Country::class);
    }

    public function findById(int $id): ?Country
    {
        return $this->cache->get(
            "country_id_{$id}",
            function (ItemInterface $item) use ($id) {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->find($id);
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findByAlpha2(string $alpha2): ?Country
    {
        return $this->cache->get(
            'country_alpha2_' . mb_strtoupper($alpha2),
            function (ItemInterface $item) use ($alpha2) {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->findOneBy(['alpha2' => mb_strtoupper($alpha2)]);
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findByAlpha3(string $alpha3): ?Country
    {
        return $this->cache->get(
            'country_alpha3_' . mb_strtoupper($alpha3),
            function (ItemInterface $item) use ($alpha3) {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->findOneBy(['alpha3' => mb_strtoupper($alpha3)]);
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findAllEnabled(): array
    {
        return $this->cache->get(
            'countries_enabled',
            function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->findBy(['enabled' => true], ['name' => 'ASC']);
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findAllAsChoices(): array
    {
        return $this->cache->get(
            'countries_choices',
            function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TTL);
                $countries = $this->findAllEnabled();
                $choices = [];
                foreach ($countries as $country) {
                    $choices[$country->getName()] = $country->getId();
                }

                return $choices;
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findAllAsApiChoices(): array
    {
        return $this->cache->get(
            'countries_api_choices',
            function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TTL);
                $countries = $this->findAllEnabled();
                $choices = [];
                foreach ($countries as $country) {
                    $choices[$country->getAlpha2()] = $country->getName();
                }

                return $choices;
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function existsEnabledByAlpha2(string $alpha2): bool
    {
        $country = $this->findByAlpha2($alpha2);

        return $country !== null && $country->isEnabled();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function existsEnabledByAlpha3(string $alpha3): bool
    {
        $country = $this->findByAlpha3($alpha3);

        return $country !== null && $country->isEnabled();
    }
}
