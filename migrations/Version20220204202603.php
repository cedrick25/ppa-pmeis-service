<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220204202603 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");

        $this->addSql("INSERT INTO services_rendered (name, created_at) VALUES "
            . "('Monitoring', '$currentDate'),"
            . "('Counseling', '$currentDate'),"
            . "('Job Referral', '$currentDate'),"
            . "('Participation in values formation activities', '$currentDate'),"
            . "('Sports/ Recreational activities', '$currentDate'),"
            . "('Engagement in community work service', '$currentDate'),"
            . "('Livelihood programs', '$currentDate'),"
            . "('Skills Training', '$currentDate'),"
            . "('RJ Processes', '$currentDate'),"
            . "('Referral to appropriate agencies or professional', '$currentDate'),"
            . "('Drug/ alcohol test', '$currentDate')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE services_rendered");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
