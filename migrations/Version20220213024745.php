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
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE social_marketing_activities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_sessions CHANGE role role enum(\'PS\', \'PR\', \'PD\', \'JICL\', \'FTMDO\', \'PET\', \'TERM\')');
        $this->addSql('ALTER TABLE clients CHANGE offense_category offense_category enum(\'DO\', \'NDO\')');
        $this->addSql('ALTER TABLE id_support CHANGE type type enum(\'VPA\', \'Personnel\'), CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE offenses CHANGE type type enum(\'DO\', \'NDO\')');
        $this->addSql('ALTER TABLE phases CHANGE name name enum(\'I\', \'II\', \'III\', \'IV\')');
        $this->addSql('ALTER TABLE quarters CHANGE name name enum(\'FIRST\', \'SECOND\', \'THIRD\', \'FOURTH\')');
        $this->addSql('ALTER TABLE resource_facilitator_session CHANGE resource_facilitator_type resource_facilitator_type enum(\'PPO\', \'VPA\', \'ERP\')');
        $this->addSql('ALTER TABLE rj_related_restitutions CHANGE rj_group rj_group enum(\'ACTIVE_SUPERVISION\', \'PETITIONER\'), CHANGE remarks remarks VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rjconduct_processes CHANGE rj_group rj_group enum(\'ACTIVE_SUPERVISION\', \'PETITIONER\'), CHANGE stakeholders stakeholders VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rjrelated_activities CHANGE rj_group rj_group enum(\'ACTIVE_SUPERVISION\', \'PETITIONER\')');
        $this->addSql('ALTER TABLE sessions CHANGE trees_planted trees_planted INT NOT NULL, CHANGE period period enum(\'AM\', \'PM\'), CHANGE li_lo li_lo enum(\'LI\', \'LO\')');
        $this->addSql('ALTER TABLE volunteer CHANGE date_appointed date_appointed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE status status enum(\'APPOINTED\', \'REAPPOINTED\',\'DROPPED\'), CHANGE date_endorsed date_endorsed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE social_marketing_activities');
        $this->addSql('ALTER TABLE client_sessions CHANGE role role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE clients CHANGE offense_category offense_category VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE id_support CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE offenses CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE phases CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE quarters CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE resource_facilitator_session CHANGE resource_facilitator_type resource_facilitator_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rj_related_restitutions CHANGE rj_group rj_group VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE remarks remarks VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rjconduct_processes CHANGE rj_group rj_group VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE stakeholders stakeholders VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rjrelated_activities CHANGE rj_group rj_group VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE sessions CHANGE trees_planted trees_planted INT DEFAULT NULL, CHANGE period period VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE li_lo li_lo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE volunteer CHANGE date_appointed date_appointed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE status status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE date_endorsed date_endorsed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
