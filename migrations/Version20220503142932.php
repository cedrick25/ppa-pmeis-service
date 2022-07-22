<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220503142932 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rjvolunteers (rj_volunteers_id INT AUTO_INCREMENT NOT NULL, volunteer_id INT NOT NULL, related_activity_id INT NOT NULL, PRIMARY KEY(rj_volunteers_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rjvolunteers');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
