<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220503181734 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vpa_association_initiated_activities (vpa_association_initiated_activity_id INT AUTO_INCREMENT NOT NULL, service_rendered_id INT NOT NULL, venue_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', venue_id INT NOT NULL, volunteer_id INT NOT NULL, role VARCHAR(255) NOT NULL, crd_resources_tapped VARCHAR(255) NOT NULL, crd_assistance_received VARCHAR(255) NOT NULL, remarks VARCHAR(255) NOT NULL, field_office_id INT NOT NULL, quarter_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(vpa_association_initiated_activity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vpa_association_initiated_activities');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
