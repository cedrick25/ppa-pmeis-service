<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220926161920 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO system_code_settings (system_code_id, name, value, created_by) VALUES 
            ('". Uuid::v4()->toRfc4122() ."', 'oic_administrator', 'Julito M. Diray', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'vpa_certificate_report_code', 'CSD-FOR-001-001', 1),
            ('". Uuid::v4()->toRfc4122() ."', 'generated_reports_code', 'PPA- PLD-FR-004', 1)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE system_code_settings");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
