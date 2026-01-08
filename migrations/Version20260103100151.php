<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260103100151 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD code VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD npn VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP code');
        $this->addSql('ALTER TABLE product DROP npn');
    }
}
