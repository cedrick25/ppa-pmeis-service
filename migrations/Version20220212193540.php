<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220212193540 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE technical_assistance (
                id INT AUTO_INCREMENT NOT NULL,
                activity_name VARCHAR(255) NOT NULL, 
                agency_name VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                venue VARCHAR(255) NOT NULL, 
                field_office_id INT NOT NULL, 
                participants_no VARCHAR(255) NOT NULL, 
                participants_type VARCHAR(255) NOT NULL, 
                personnel_id INT DEFAULT NULL, 
                personnel_role VARCHAR(255) DEFAULT NULL, 
                vpa_id INT DEFAULT NULL,  
                vpa_role VARCHAR(255) DEFAULT NULL, 
                remarks VARCHAR(255) DEFAULT NULL, 
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE technical_assistance');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
