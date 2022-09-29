<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220928015913 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_sessions ADD fsg_number INT DEFAULT NULL, CHANGE other_remarks other_remarks LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sessions DROP remarks_id, CHANGE trees_planted trees_planted INT NOT NULL');
        $this->addSql('ALTER TABLE sessions DROP role');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_sessions DROP fsg_number, CHANGE other_remarks other_remarks TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sessions ADD remarks_id INT NOT NULL, CHANGE trees_planted trees_planted INT DEFAULT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
