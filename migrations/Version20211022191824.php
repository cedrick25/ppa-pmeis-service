<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211022191824 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");

        $this->addSql("INSERT INTO phases (name, sort_order, created_at) VALUES "
            . "('I', 1, '$currentDate'),"
            . "('II', 2, '$currentDate'),"
            . "('III', 3, '$currentDate'),"
            . "('IV', 4, '$currentDate')"
        );

        $this->addSql("INSERT INTO treatment_categories (name, created_at) VALUES "
            . "('Pre-Morning Meeting', '$currentDate'),"
            . "('Morning Meeting', '$currentDate'),"
            . "('Job Functions', '$currentDate'),"
            . "('PD968 Implementation', '$currentDate'),"
            . "('Individual Counseling', '$currentDate')"
        );

        $this->addSql("INSERT INTO session_activities (name, created_at) VALUES "
            . "('MTCS-RBM', '$currentDate'),"
            . "('MTCS-AEP', '$currentDate'),"
            . "('MTCS-S', '$currentDate'),"
            . "('MTCS-CI', '$currentDate'),"
            . "('MTCS-PVS', '$currentDate'),"
            . "('RA-RBM', '$currentDate'),"
            . "('RA-AEP', '$currentDate'),"
            . "('RA-S', '$currentDate'),"
            . "('RA-CI', '$currentDate'),"
            . "('RA-PVS', '$currentDate')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE phases");
        $this->addSql("TRUNCATE TABLE session_activities");
        $this->addSql("TRUNCATE TABLE treatment_categories");
    }
}
