<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220127193527 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rjrelated_activities (rj_related_activity_id INT AUTO_INCREMENT NOT NULL, quarter_id INT NOT NULL, field_office_id INT NOT NULL, client_id INT NOT NULL, offense_id INT NOT NULL, pe_date DATE NOT NULL, pe_venue_id INT NOT NULL, victims VARCHAR(255) NOT NULL, rjp_id INT NOT NULL, venue_id INT NOT NULL, rjo_id INT NOT NULL, rj_group enum(\'ACTIVE_SUPERVISION\', \'PETITIONER\'), created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(rj_related_activity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rjrelated_activities');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
