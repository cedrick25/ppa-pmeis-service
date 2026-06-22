<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20260617070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CMIS Form 5 Table 7 client sync fields and defaults.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clients CHANGE cmis_id cmis_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE clients ADD cmis_docket_no VARCHAR(50) DEFAULT NULL, ADD cmis_case_classification VARCHAR(50) DEFAULT NULL, ADD cmis_y_m VARCHAR(7) DEFAULT NULL, ADD cmis_synced_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD cmis_last_seen_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_CLIENTS_CMIS_ID ON clients (cmis_id)');
        $this->addSql("INSERT INTO system_code_settings (system_code_id, name, value, created_by) VALUES
            ('". Uuid::v4()->toRfc4122() ."', 'CMIS Default Client Type Code', 'PS', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'CMIS Default Gender', 'M', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'CMIS Default Date Of Birth', '1900-01-01', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'CMIS Default PWD', 'false', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'CMIS Default Senior Citizen', 'false', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'CMIS Default Offense Category', 'NDO', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'CMIS Default Client Remarks ID', '1', 1)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM system_code_settings WHERE name IN (
            'CMIS Default Client Type Code',
            'CMIS Default Gender',
            'CMIS Default Date Of Birth',
            'CMIS Default PWD',
            'CMIS Default Senior Citizen',
            'CMIS Default Offense Category',
            'CMIS Default Client Remarks ID'
        )");
        $this->addSql('DROP INDEX IDX_CLIENTS_CMIS_ID ON clients');
        $this->addSql('ALTER TABLE clients DROP cmis_docket_no, DROP cmis_case_classification, DROP cmis_y_m, DROP cmis_synced_at, DROP cmis_last_seen_at');
        $this->addSql('ALTER TABLE clients CHANGE cmis_id cmis_id INT NOT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
