<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221121212952 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE special_assignment_personnel_involved (id INT AUTO_INCREMENT NOT NULL, special_assignment_id INT NOT NULL, personnel_involved_id INT NOT NULL, type VARCHAR(255) NOT NULL, others_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE special_assignment_personnel_involved');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
