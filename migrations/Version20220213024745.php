<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213024745 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE social_marketing_activities (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO social_marketing_activities (name, type) VALUES
            ('Fora,symposia (Planned, coordinated, organized with program and topics, personnel/ VPAs with specific roles, letter, request (if applicable), with target number of participants and content limited to programs and services of the Agency.)', 'INFORMATION_DISSEMINATION'),
            ('Publication', 'INFORMATION_DISSEMINATION'),
            ('Press Releases', 'INFORMATION_DISSEMINATION'),
            ('TV', 'INFORMATION_DISSEMINATION'),
            ('Radio Interviews', 'INFORMATION_DISSEMINATION'),
            ('Guestings Screen reader support enabled.', 'INFORMATION_DISSEMINATION'),
            ('Peace and Order Councils (POC) / Anti-Drug Abuse Council ( CADAC)/Government Information Officers  League, MSEC, etc.', 'MEETINGS_PARTICIPATIONS'),
            ('Others (Attendance/ Participation in significant events as representative of the Agency)', 'MEETINGS_PARTICIPATIONS')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE social_marketing_activities');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
