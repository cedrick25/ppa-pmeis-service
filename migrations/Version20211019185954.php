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
            'NCR', 'Region I', 'CAR', 'Region II', 'Region III', 'Region IV-A',
            'Region IV-B', 'Region V', 'Region VI', 'NIR or Region XVIII', 'Region VII',
            'Region VIII', 'Region IX', 'Region X', 'Region XIII', 'Region XI', 'Region XII',
            'ARMM'
        ];
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
