<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220208212008 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE session_remarks (session_remark_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(session_remark_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("INSERT INTO session_remarks (name) VALUES "
            . "('Tree Planting'),"
            . "('Community Services and Other Related Activities'),"
            . "('Coop./ Self-Help Asso.')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE session_remarks');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
