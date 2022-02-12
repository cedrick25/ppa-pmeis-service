<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220212101758 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE client_remarks (client_remarks_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(client_remarks_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO client_remarks (name) VALUES "
            . "('w/ FR/VR'),"
            . "('Terminated'),"
            . "('Revoked'),"
            . "('on CS'),"
            . "('Transferred'),"
            . "('Absconded'),"
            . "('Died'),"
            . "('In Jail with no report'),"
            . "('With Serious Ailment'),"
            . "('On Travel Abroad (with permit)'),"
            . "('Case/ s pending in Court'),"
            . "('Others'),"
            . "('On CS to other FOs')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE client_remarks');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
