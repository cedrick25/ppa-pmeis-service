<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add full_name column to clients for CMIS probationer-only records.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clients ADD full_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clients DROP full_name');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
