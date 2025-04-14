<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414185300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO "user" (email, name, password, roles, force_password_change, force_email_change)
            VALUES ('admin@example.com', 'Admin', '$2y$13$5KIVXUZbtjXtTxa39ZWGqONp8y6CkhR74iVM276m6TScMnrJiwvu.', '["ROLE_ADMIN"]', true, true)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DELETE FROM "user" WHERE email = 'admin@example.com'
        SQL);
    }

}
