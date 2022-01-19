<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110150622 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO client_types (code, description) VALUES "
            . "('PS', 'Probationers'),"
            . "('PR', 'Parolees'),"
            . "('PD', 'Pardonees'),"
            . "('JICL', 'JICLs'),"
            . "('FTMDC', 'FTMDOs'),"
            . "('Pet', 'Petitioners'),"
            . "('Term', 'Terminated')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE client_types");
    }
}
