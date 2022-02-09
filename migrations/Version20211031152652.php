<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211031152652 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE volunteer (
            volunteer_id INT AUTO_INCREMENT NOT NULL, 
            first_name VARCHAR(255) NOT NULL, 
            middle_name VARCHAR(255) DEFAULT NULL, 
            last_name VARCHAR(255) NOT NULL, 
            suffix VARCHAR(5) DEFAULT NULL, 
            gender VARCHAR(1) NOT NULL, 
            date_of_birth VARCHAR(255) NOT NULL, 
            is_senior_citizen TINYINT(1) DEFAULT NULL, 
            is_pwd TINYINT(1) DEFAULT NULL, 
            field_office_id INT DEFAULT NULL,
            date_recruited DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            recruiting_officer VARCHAR(255) NOT NULL,
            age INT DEFAULT NULL,
            birth_place VARCHAR(255) NOT NULL,
            civil_status INT DEFAULT NULL,
            religion INT DEFAULT NULL,
            present_address VARCHAR(255) NOT NULL,
            height DOUBLE PRECISION NOT NULL,
            weight DOUBLE PRECISION NOT NULL,
            blood_type VARCHAR(255) NOT NULL,
            occupation INT DEFAULT NULL,
            education_attainment INT DEFAULT NULL,
            contact_number VARCHAR(255) NOT NULL,
            email_address VARCHAR(255) NOT NULL,
            domestic_partner VARCHAR(255) NULL,
            special_skill VARCHAR(255) NOT NULL,
            emergency_name VARCHAR(255) NOT NULL,
            emergency_number VARCHAR(255) NOT NULL,
            image VARCHAR(255) NOT NULL,
            applicant_signature VARCHAR(255) NOT NULL,
            date_accomplished DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            officer_signature VARCHAR(255) NOT NULL,
            date_signed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            date_appointed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            vpa_status VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            PRIMARY KEY(volunteer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE volunteer');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
