<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220214013929 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE support_of_region_to_field_office (id INT AUTO_INCREMENT NOT NULL, category enum(\'TC\', \'RJ\', \'VPA\', \'GAD\', \'PWDSC\', \'OTHERS\'), sub_category VARCHAR(255) NOT NULL, date DATE NOT NULL, region_id INT NOT NULL, particulars VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, attributable_cost VARCHAR(255) NOT NULL, total_amount DOUBLE PRECISION NOT NULL, remarks VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE support_of_region_to_field_office');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
