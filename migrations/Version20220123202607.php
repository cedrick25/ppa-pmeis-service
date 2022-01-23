<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220123202607 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");

        $this->addSql("INSERT INTO rjoutcomes (name, code, created_at) VALUES "
            . "('Restitution', 'R', '$currentDate'),"
            . "('Community Work Services', 'CWS', '$currentDate'),"
            . "('Restored Relationships', 'RR', '$currentDate'),"
            . "('Others', 'O', '$currentDate')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE rjoutcomes");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
