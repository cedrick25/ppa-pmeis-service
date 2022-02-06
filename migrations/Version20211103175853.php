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
            . "('Manila City Parole And Probation Office No. 6', 1, '{$currentDate}'),"
            . "('Baybay City Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Calbayog City Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Maasin City Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Ormoc City Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Tacloban City Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Biliran Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Eastern Samar Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Leyte Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Northern Samar Parole And Probation Office No', 12,'{$currentDate}'),"
            . "('Samar Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Southern Leyte Parole And Probation Office', 12, '{$currentDate}'),"
            . "('Cotabato City Parole And Probation Office', 11, '{$currentDate}'),"
            . "('General Santos City Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Kidapawan City Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Koronadal City Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Marawi City Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Tacurong City Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Lanao Del Sur Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Maguindanao Parole And Probation Office', 11, '{$currentDate}'),"
            . "('North Cotabato Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Sarangani Parole And Probation Office', 11, '{$currentDate}'),"
            . "('South Cotabato Parole And Probation Office', 11, '{$currentDate}'),"
            . "('Sultan Kudarat Parole And Probation Office', 11, '{$currentDate}'),"
            . "('', 1, '{$currentDate}'),"
            . "('', 1, '{$currentDate}')");
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
