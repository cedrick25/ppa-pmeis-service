<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220302164142 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE volunteer_supervisions (volunteer_supervisions_id INT AUTO_INCREMENT NOT NULL, volunteer_id INT NOT NULL, services_rendered_id INT NOT NULL, community_resources_tapped VARCHAR(255) DEFAULT NULL, assistance_received VARCHAR(255) DEFAULT NULL, remarks VARCHAR(255) DEFAULT NULL, field_office_id INT DEFAULT NULL, quarter_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(volunteer_supervisions_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE volunteer_supervisions');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
