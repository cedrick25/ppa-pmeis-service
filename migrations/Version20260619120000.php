<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cmis_source for multi-table CMIS client sync.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clients ADD cmis_source VARCHAR(30) DEFAULT NULL');
        $this->addSql("UPDATE clients SET cmis_source = 'f5t7' WHERE cmis_id IS NOT NULL AND cmis_source IS NULL");
        $this->addSql('CREATE INDEX IDX_CLIENTS_CMIS_SOURCE_ID ON clients (cmis_source, cmis_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_CLIENTS_CMIS_SOURCE_ID ON clients');
        $this->addSql('ALTER TABLE clients DROP cmis_source');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
