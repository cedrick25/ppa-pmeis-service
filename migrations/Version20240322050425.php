<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240322050425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients ADD COLUMN alias VARCHAR(255) DEFAULT NULL AFTER suffix, CHANGE gender gender VARCHAR(1) DEFAULT NULL, CHANGE date_of_birth date_of_birth VARCHAR(255) DEFAULT NULL, CHANGE offense_category offense_category VARCHAR(3) DEFAULT NULL, CHANGE is_senior_citizen is_senior_citizen TINYINT(1) DEFAULT NULL, CHANGE is_pwd is_pwd TINYINT(1) DEFAULT NULL, CHANGE supervision_start supervision_start DATE DEFAULT NULL, CHANGE supervision_end supervision_end DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients DROP alias, CHANGE gender gender VARCHAR(1) NOT NULL, CHANGE date_of_birth date_of_birth VARCHAR(255) NOT NULL, CHANGE offense_category offense_category VARCHAR(3) NOT NULL, CHANGE is_senior_citizen is_senior_citizen TINYINT(1) NOT NULL, CHANGE is_pwd is_pwd TINYINT(1) NOT NULL, CHANGE supervision_start supervision_start DATE NOT NULL, CHANGE supervision_end supervision_end DATE NOT NULL');
    }
}
