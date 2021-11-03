<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211103175853 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");
        $this->addSql("INSERT INTO field_offices (name, region_id ,created_at) VALUES "
            . "('Quezon City Parole And Probation Office No. 1', 1, '{$currentDate}'),"
            . "('Quezon City Parole And Probation Office No. 2', 1, '{$currentDate}'),"
            . "('Manila City Parole And Probation Office No. 3', 1, '{$currentDate}'),"
            . "('Manila City Parole And Probation Office No. 6', 1, '{$currentDate}')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE field_offices");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
