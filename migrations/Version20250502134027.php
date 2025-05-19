<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250502134027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ALTER enabled SET DEFAULT true
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ALTER enabled DROP DEFAULT
        SQL);
    }
}
