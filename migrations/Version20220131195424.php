<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220131195424 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE volunteer_id (id_no INT AUTO_INCREMENT NOT NULL, volunteer_id INT NOT NULL, admin_name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id_no)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_operations (volunteer_operation_id INT AUTO_INCREMENT NOT NULL, volunteer_id INT NOT NULL, status enum(\'APPOINTED\', \'REAPPOINTED\',\'DROPPED\'), date DATE NOT NULL, reason VARCHAR(255) DEFAULT NULL, dropped_by INT NOT NULL, PRIMARY KEY(volunteer_operation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE volunteer CHANGE domestic_partner domestic_partner VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE status status enum(\'APPOINTED\', \'REAPPOINTED\', \'DROPPED\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE volunteer_id');
        $this->addSql('DROP TABLE volunteer_operations');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
