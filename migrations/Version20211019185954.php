<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211019185954 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql($this->buildSql($this->getRegions()));
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE regions");
    }

    /**
     * @param string[] $data
     * @return string
     */
    private function buildSql(array $data): string
    {
        $currentDate = date("Y-m-d H:m:s");
        $sql = "INSERT INTO regions (name, created_at) VALUES ";
        foreach ($data as $item) {
            $sql .= "('{$item}', '{$currentDate}'),";
        }
        return rtrim($sql, ',');
    }

    /**
     * @return string[]
     */
    private function getRegions(): array
    {
        return [
            'NCR', 'Regions I', 'CAR', 'Regions II', 'Regions III', 'Regions IV-A',
            'Regions IV-B', 'Regions V', 'Regions VI', 'NIR or Regions XVIII', 'Regions VII',
            'Regions VIII', 'Regions IX', 'Regions X', 'Regions XIII', 'Regions XI', 'Regions XII',
            'ARMM'
        ];
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
