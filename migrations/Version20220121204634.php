<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220121204634 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rjconduct_processes (
                        rj_conduct_process_id INT AUTO_INCREMENT NOT NULL,
                        client_id INT NOT NULL,
                        quarter_id INT NOT NULL,
                        field_office_id INT NOT NULL,
                        offense_id INT NOT NULL,
                        pe_date DATE NOT NULL,
                        pe_venue_id INT NOT NULL,
                        pe_activity VARCHAR(255) NOT NULL,
                        rjp_date DATE NOT NULL,
                        rjp_id INT NOT NULL,
                        rjp_venue_id INT NOT NULL,
                        rjps_id INT NOT NULL,
                        rjo_id INT NOT NULL,
                        rj_group enum(\'ACTIVE_SUPERVISION\', \'PETITIONER\'),
                        planner_id INT NOT NULL,
                        stakeholders VARCHAR(255) DEFAULT NULL,
                        created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                        updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                        deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                        PRIMARY KEY(rj_conduct_process_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rjconduct_processes CHANGE rj_group rj_group enum(\'ACTIVE_SUPERVISION\', \'PETITIONER\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rjconduct_processes');
        $this->addSql('ALTER TABLE rjconduct_processes CHANGE rj_group rj_group VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
