<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022124733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appuser DROP force_email_change');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appuser ADD force_email_change BOOLEAN DEFAULT false NOT NULL');
    }
}
