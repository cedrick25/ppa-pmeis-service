<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220129183951 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rj_related_restitutions (rj_related_restitution_id INT AUTO_INCREMENT NOT NULL, quarter_id INT NOT NULL, field_office_id INT NOT NULL, client_id INT NOT NULL, rj_group enum(\'ACTIVE_SUPERVISION\', \'PETITIONER\'), offense_id INT NOT NULL, original_amount DOUBLE PRECISION NOT NULL, start_of_quarter DOUBLE PRECISION NOT NULL, amount_paid DOUBLE PRECISION NOT NULL, balance DOUBLE PRECISION NOT NULL, payment_form_id INT NOT NULL, payment_mode_id INT NOT NULL, payment_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', payment_amount DOUBLE PRECISION NOT NULL, payment_recipient VARCHAR(255) NOT NULL, remitted_to VARCHAR(255) NOT NULL, remitted_amount DOUBLE PRECISION NOT NULL, remarks VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(rj_related_restitution_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rj_related_restitutions');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
