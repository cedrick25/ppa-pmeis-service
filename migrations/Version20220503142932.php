<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\RjRelatedActivitiesPersonsInvolved;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220503142932 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rj_related_activities_persons_involved (id INT AUTO_INCREMENT NOT NULL, related_activity_id INT NOT NULL, persons_involved_id INT NOT NULL, type VARCHAR(255) NOT NULL, others_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rj_related_activities_persons_involved');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
