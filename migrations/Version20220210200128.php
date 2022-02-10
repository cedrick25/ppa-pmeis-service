<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210200128 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO active_supervision_remarks (name) VALUES "
            . "('w/ FR/VR'),"
            . "('Terminated'),"
            . "('Revoked'),"
            . "('on CS'),"
            . "('Transferred'),"
            . "('Absconded'),"
            . "('Died'),"
            . "('Others')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE active_supervision_remarks');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
