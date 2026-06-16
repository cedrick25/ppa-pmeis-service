<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715155503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE session_selected_activities (session_selected_activity_id INT AUTO_INCREMENT NOT NULL, session_activity_id INT NOT NULL, session_id INT NOT NULL, treatment_category_id INT NOT NULL, is_community_service TINYINT(1) NOT NULL, is_tree_planting TINYINT(1) NOT NULL, is_cooperative_self_help TINYINT(1) NOT NULL, is_cooperative_self_help_activities TINYINT(1) NOT NULL, PRIMARY KEY(session_selected_activity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('ALTER TABLE clients CHANGE gender gender VARCHAR(1) NOT NULL, CHANGE date_of_birth date_of_birth VARCHAR(255) NOT NULL, CHANGE offense_category offense_category VARCHAR(3) NOT NULL, CHANGE is_senior_citizen is_senior_citizen TINYINT(1) NOT NULL, CHANGE is_pwd is_pwd TINYINT(1) NOT NULL, CHANGE supervision_start supervision_start DATE NOT NULL, CHANGE supervision_end supervision_end DATE NOT NULL');
        // $this->addSql('ALTER TABLE resource_facilitator_session CHANGE role role LONGTEXT DEFAULT NULL, CHANGE erp_name erp_name LONGTEXT DEFAULT NULL');
        // $this->addSql('ALTER TABLE social_marketing CHANGE field_office_id field_office_id INT DEFAULT NULL');
        // $this->addSql('ALTER TABLE volunteer CHANGE date_appointed date_appointed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        // $this->addSql('ALTER TABLE volunteer_operations CHANGE date_endorsed date_endorsed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE session_selected_activities');
        $this->addSql('ALTER TABLE clients CHANGE gender gender VARCHAR(1) DEFAULT NULL, CHANGE date_of_birth date_of_birth VARCHAR(255) DEFAULT NULL, CHANGE offense_category offense_category VARCHAR(3) DEFAULT NULL, CHANGE is_senior_citizen is_senior_citizen TINYINT(1) DEFAULT NULL, CHANGE is_pwd is_pwd TINYINT(1) DEFAULT NULL, CHANGE supervision_start supervision_start DATE DEFAULT NULL, CHANGE supervision_end supervision_end DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_facilitator_session CHANGE role role TEXT DEFAULT NULL, CHANGE erp_name erp_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE social_marketing CHANGE field_office_id field_office_id INT NOT NULL');
        $this->addSql('ALTER TABLE volunteer CHANGE date_appointed date_appointed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE date_endorsed date_endorsed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
