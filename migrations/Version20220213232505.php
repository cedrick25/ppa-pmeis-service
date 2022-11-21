<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213232505 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jail_decongestion (jail_decongestion_id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, name_address VARCHAR(255) NOT NULL, jail_venue TINYINT(1) NOT NULL, jail_office TINYINT(1) DEFAULT NULL, probation INT DEFAULT NULL, clemency INT DEFAULT NULL, referral_pao INT DEFAULT NULL, referral_prosecution INT DEFAULT NULL, referral_others INT DEFAULT NULL, gcta INT DEFAULT NULL, recognizance INT DEFAULT NULL, remarks VARCHAR(255) NOT NULL, field_office_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(jail_decongestion_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jail_decongestion');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
