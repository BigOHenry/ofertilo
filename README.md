# Ofertilo

**Ofertilo** je interní nástroj pro správu zakázek, evidenci materiálů a tvorbu cenových nabídek. Byl vytvořen jako podpora výrobního procesu mého e-shopu [woodflag.eu](https://woodflag.eu) a zároveň jako ukázka programátorských dovedností v PHP.

## ✨ Funkce
- Evidence klientů a zakázek
- Výpočet spotřeby a ceny materiálů (masiv, MDF, překližka, spárovky...) (plánováno)
- Tvorba cenových nabídek s různými variantami (materiál, rozměr, povrchová úprava) (plánováno)
- Víceúrovňové výpočty a varianty provedení (plánováno)
- Šablony cenových nabídek (plánováno)
- Export do PDF (plánováno)
- Link pro zobrazení nacenění klientovi (plánováno)

## 🛠️ Technologie
- PHP 8.4.x
- Bootstrap 5.3
- PostgreSQL 17.4

## 📦 Instalace

- vytvoření env.docker z env.docker.example a úprava hesla
- spuštění docker compose up -d --build
- vytvoření env.local z env.local.example
- composer install
- npm run dev
- spuštění mígrací php bin/console doctrine:migrations:migrate
