# Ofertilo

**Ofertilo** je interní nástroj pro správu zakázek, evidenci materiálů a tvorbu cenových nabídek. Byl vytvořen jako podpora výrobního procesu mého e-shopu [woodflag.eu](https://woodflag.eu) a zároveň jako ukázka programátorských dovedností v PHP.

![Coverage](https://github.com/BigOHenry/ofertilo/blob/image-data/coverage.svg)

## ✨ Funkce
- [x] Evidence materiálů a jejich cen dle tloušťky (MDF/překližka, dřevo, spárovky)
- [x] Evidence barev (barvy vzorníku RAL)
- [x] Evidence produktů a přiřazení použitých barev s popiskem
- [ ] Evidence klientů včetně kontaktu
- [ ] Tvorba cenových nabídek s různými variantami (materiál, rozměr, povrchová úprava)
- [ ] Výpočet spotřeby a ceny materiálů (masiv, MDF, překližka, spárovky...)
- [ ] Šablony cenových nabídek
- [ ] Export do PDF
- [ ] Link pro zobrazení nacenění klientovi
- [ ] Nacenění variant produktů na e-shop včetně možnosti aktualizovat produkty přímo na e-shopu (API)

## 🛠️ Technologie
- PHP 8.4.x
- Bootstrap 5.3
- PostgreSQL 17.5
- Symfony 7.x
- Doctrine ORM
- Tabulator.js
- Webpack

## 📦 Instalace

- vytvoření env.docker z env.docker.example a úprava hesla
- spuštění `docker compose up -d --build`
- vytvoření `env.local` z `env.local.example`
- `composer install`
- `npm run dev`
- vygenerování migrací `php bin/console make:migration`
- spuštění mígrací `php bin/console doctrine:migrations:migrate`

## 📦 CS + PHPStan + PHPUnit

- kvalita kódu je kontrolována dle PSR-12 pomocí CodeSniffer `vendor/bin/phpcs` nebo automatický fix `PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix`
- statickou analýzu kódu provádí PHPStan `vendor/bin/phpstan analyse`
- 
