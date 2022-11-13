<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221112103348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pmd_person_responsible (id INT AUTO_INCREMENT NOT NULL, pmd_id INT NOT NULL, person_responsible_id INT NOT NULL, type VARCHAR(255) NOT NULL, others_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE id_support CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE program_materials_development DROP person_responsible_type, DROP vpa_ppo_id');
        $this->addSql('ALTER TABLE resource_facilitator_session CHANGE role role LONGTEXT DEFAULT NULL, CHANGE erp_name erp_name LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_mobilization CHANGE materials_amount materials_amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE rj_related_restitutions CHANGE remarks remarks VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE social_marketing CHANGE field_office_id field_office_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account CHANGE user_type user_type VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE volunteer CHANGE date_appointed date_appointed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE date_endorsed date_endorsed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pmd_person_responsible');
        $this->addSql('ALTER TABLE id_support CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE program_materials_development ADD person_responsible_type VARCHAR(255) NOT NULL, ADD vpa_ppo_id INT NOT NULL');
        $this->addSql('ALTER TABLE resource_facilitator_session CHANGE role role TEXT DEFAULT NULL, CHANGE erp_name erp_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_mobilization CHANGE materials_amount materials_amount DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE rj_related_restitutions CHANGE remarks remarks VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE social_marketing CHANGE field_office_id field_office_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_account CHANGE user_type user_type VARCHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE volunteer CHANGE date_appointed date_appointed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE date_endorsed date_endorsed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
