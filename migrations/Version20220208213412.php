<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220208213412 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE civil_status (civil_status_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(civil_status_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO civil_status (name) VALUES "
            . "('Single'),"
            . "('Married'),"
            . "('Widowed'),"
            . "('Seperated'),"
            . "('Not Indicated')"
        );

        $this->addSql('CREATE TABLE education_background (education_background_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(education_background_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO education_background (name) VALUES "
            . "('Post Graduate'),"
            . "('College Graduate'),"
            . "('College Level'),"
            . "('Vocational'),"
            . "('HS Graduate'),"
            . "('HS Level'),"
            . "('Elementary'),"
            . "('Not Indicated')"
        );

        $this->addSql('CREATE TABLE religion (religion_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(religion_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO religion (name) VALUES "
            . "('Roman Catholic'),"
            . "('INC'),"
            . "('Islam'),"
            . "('Others'),"
            . "('Not Indicated')"
        );

        $this->addSql('CREATE TABLE occupation (occupation_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(occupation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO occupation (name) VALUES
            ('Armed Forces Occupation'),
            ('Managers'),
            ('Professionals'),
            ('Technical Associate Professionals'),
            ('Clerical Support Workers'),
            ('Service and Sales Workers'),
            ('Skilled Agricultural, Forestry and Fishery Workers'),
            ('Craft and Related Trades Workers'),
            ('Plant and Machines Operators and Assemblers'),
            ('Elementary Occupation'),
            ('Unemployed')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE civil_status');
        $this->addSql('DROP TABLE education_background');
        $this->addSql('DROP TABLE religion');
        $this->addSql('DROP TABLE occupation');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
