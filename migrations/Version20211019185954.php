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
        $this->addSql($this->buildSql($this->getRegions(), 'regions'));
        $this->addSql($this->buildSql($this->getFieldOffices(), 'field_offices'));
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE regions");
        $this->addSql("TRUNCATE TABLE field_offices");
    }

    /**
     * @param string[] $data
     * @param string $table
     * @return string
     */
    private function buildSql(array $data, string $table): string
    {
        $currentDate = date("Y-m-d H:m:s");
        $sql = "INSERT INTO {$table} (name, created_at) VALUES ";
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
            'ARMM', '12', 'region', 'Region 69', 'Region 701', 'Region 70001'
        ];
    }

    /**
     * @return string[]
     */
    private function getFieldOffices(): array
    {
        return [
            'Baybay City Parole And Probation Office', 'Calbayog City Parole And Probation Office', 'Maasin City Parole And Probation Office',
            'Ormoc City Parole And Probation Office', 'Tacloban City Parole And Probation Office', 'Biliran Parole And Probation Office',
            'Eastern Samar Parole And Probation Office', 'Leyte Parole And Probation Office', 'Northern Samar Parole And Probation Office No. 1',
            'Northern Samar Parole And Probation Office No. 2', 'Samar Parole And Probation Office', 'Southern Leyte Parole And Probation Office',
            'Cotabato City Parole And Probation Office', 'General Santos City Parole And Probation Office', 'Kidapawan City Parole And Probation Office',
            'Koronadal City Parole And Probation Office', 'Marawi City Parole And Probation Office', 'Tacurong City Parole And Probation Office',
            'Lanao Del Sur Parole And Probation Office', 'Maguindanao Parole And Probation Office', 'North Cotabato Parole And Probation Office',
            'Sarangani Parole And Probation Office', 'South Cotabato Parole And Probation Office', 'Sultan Kudarat Parole And Probation Office',
            'Iriga City Parole And Probation Office', 'Legaspi City Parole And Probation Office', 'Ligao City Parole And Probation Office',
            'Masbate City Parole And Probation Office', 'Naga City Parole And Probation Office', 'Sorsogon City Parole And Probation Office',
            'Tabaco City Parole And Probation Office', 'Albay Parole And Probation Office', 'Camarines Norte Parole And Probation Office',
            'Camarines Sur Parole And Probation Office No. 1', 'Camarines Sur Parole And Probation Office No. 2', 'Catanduanes Parole And Probation Office',
            'Masbate Province Parole And Probation Office', 'Sorsogon Province Parole And Probation Office', 'Cauayan City Parole And Probation Office',
            'Ilagan City Parole And Probation Office', 'Santiago City Parole And Probation Office', 'Tuguegarao City Parole And Probation Office',
            'Cagayan Parole And Probation Office No. 1', 'Cagayan Parole And Probation Office No. 2', 'Isabela Parole And Probation Office',
            'Nueva Vizcaya Parole And Probation Office', 'Quirino Parole And Probation Office', 'Calapan City Parole And Probation Office',
            'Puerto Princesa City Parole And Probation Office', 'Marinduque Parole And Probation Office', 'Occidental Mindoro Parole And Probation Office',
            'Oriental Mindoro Parole And Probation Office', 'Palawan Parole And Probation Office No. 1', 'Palawan Parole And Probation Office No. 2',
            'Romblon Parole And Probation Office', 'Bacolod City Parole And Probation Office No. 1', 'Bacolod City Parole And Probation Office No. 2',
            'Bago City Parole And Probation Office', 'Cadiz City Parole And Probation Office', 'Escalante City Parole And Probation Office',
            'Himamaylan City Parole And Probation Office', 'Iloilo City Parole And Probation Office', 'Kabankalan City Parole And Probation Office',
            'La Carlota City Parole And Probation Office', 'Passi City Parole And Probation Office', 'Roxas City Parole And Probation Office',
            'Sagay City Parole And Probation Office', 'San Carlos City Parole And Probation Office',
            'Silay City Parole And Probation Office', 'Sipalay City Parole And Probation Office',
            'Talisay City Parole And Probation Office', 'Victorias City Parole And Probation Office', 'Aklan Parole And Probation Office',
            'Antique Parole And Probation Office', 'Capiz Parole And Probation Office', 'Guimaras Parole And Probation Office',
            'Iloilo Province Parole And Probation Office No. 1', 'Iloilo Province Parole And Probation Office No. 2', 'Iloilo Province Parole And Probation Office No. 3',
            'Baguio City Parole And Probation Office', 'Abra Parole And Probation Office', 'Apayao Parole And Probation Office',
            'Benguet Parole And Probation Office', 'Ifugao Parole And Probation Office', 'Kalinga Parole And Probation Office',
            'Mt. Province Parole And Probation Office', 'Dapitan City Parole And Probation Office', 'Dipolog City Parole And Probation Office',
            'Pagadian City Parole And Probation Office', 'Zamboanga City Parole And Probation Office No. 1', 'Zamboanga City Parole And Probation Office No. 2',
            'Sulu Parole And Probation Office', 'Zamboanga Del Sur Parole And Probation Office No. 1', 'Zamboanga Del Sur Parole And Probation Office No. 2',
            'Zamboanga Sibugay Parole And Probation Office', 'Zamboanga Del Norte Parole And Probation Office', 'Cagayan De Oro City Parole And Probation Office',
            'Gingoog City Parole And Probation Office', 'Iligan City Parole And Probation Office', 'Malaybalay City Parole And Probation Office',
            'Oroquieta City Parole And Probation Office', 'Tangub City Parole And Probation Office', 'Valencia City Parole And Probation Office',
            'Bukidnon Parole And Probation Office No. 1', 'Bukidnon Parole And Probation Office No. 2', 'Camiguin Parole And Probation Office',
            'Lanao Del Norte Parole And Probation Office', 'Misamis Occidental Parole And Probation Office', 'Misamis Oriental Parole And Probation Office',
            'Ozamiz City Parole And Probation Office', 'Alaminos City Parole And Probation Office', 'Candon City Parole And Probation Office',
            'Dagupan City Parole And Probation Office', 'Laoag City Parole And Probation Office', 'San Fernando City Parole And Probation Office',
            'Urdaneta City Parole And Probation Office', 'Vigan City/Ilocos Sur Parole And Probation Office', 'Ilocos Norte Parole And Probation Office',
            'La Union Province Parole And Probation Office', 'Pangasinan Parole And Probation Office No. 1', 'Pangasinan Parole And Probation Office No. 2',
            'Bislig City Parole And Probation Office', 'Butuan City Parole And Probation Office', 'Surigao City Parole And Probation Office',
            'Tandag City Parole And Probation Office', 'Agusan Del Norte Parole And Probation Office', 'Agusan Del Sur Parole And Probation Office',
            'Surigao Del Norte Parole And Probation Office No. 1', 'Surigao Del Norte Parole And Probation Office No. 2',
            'Surigao Del Sur Parole And Probation Office', 'Bais City Parole And Probation Office', 'Bayawan City Parole And Probation Office',
            'Bogo City Parole And Probation Office', 'Canlaon City Parole And Probation Office', 'Carcar City Parole And Probation Office',
            'Cebu City Parole And Probation Office No. 1', 'Cebu City Parole And Probation Office No. 2', 'City Of Naga Parole And Probation Office',
            'Danao City Parole And Probation Office', 'Dumaguete City Parole And Probation Office', 'Guihulngan City Parole And Probation Office',
            'Lapu-lapu City Parole And Probation Office', 'Mandaue City Parole And Probation Office', 'Tagbilaran City Parole And Probation Office',
            'Tanjay City Parole And Probation Office', 'Toledo City Parole And Probation Office', 'Bohol Province Parole And Probation Office No. 1',
            'Bohol Province Parole And Probation Office No. 2', 'Cebu Province Parole And Probation Office', 'Negros Oriental Province Parole And Probation Office',
            'Siquijor Province Parole And Probation Office', 'Antipolo City Parole And Probation Office', 'Batangas City Parole And Probation Office',
            'Calamba City Parole And Probation Office', 'Cavite City Parole And Probation Office', 'Lipa City Parole And Probation Office',
            'Lucena City Parole And Probation Office', 'San Pablo City Parole And Probation Office', 'Sta. Rosa City Parole And Probation Office',
            'Tagaytay City Parole And Probation Office', 'Tanauan City Parole And Probation Office', 'Trece Martires City Parole And Probation Office',
            'Batangas Province Parole And Probation Office', 'Cavite Province Parole And Probation Office', 'Laguna Province Parole And Probation Office',
            'Quezon Province Parole And Probation Office No. 1', 'Quezon Province Parole And Probation Office No. 2', 'Quezon Province Parole And Probation Office No. 3',
            'Rizal Parole And Probation Office No. 1', 'Rizal Parole And Probation Office No. 2', 'Caloocan City Parole And Probation Office',
            'Las Pinas City Parole And Probation Office', 'Makati City Parole And Probation Office', 'Malabon City/Navotas City Parole And Probation Office',
            'Mandaluyong City/San Juan Parole And Probation Office', 'Manila City Parole And Probation Office No. 1', 'Manila City Parole And Probation Office No. 2',
            'Manila City Parole And Probation Office No. 3', 'Manila City Parole And Probation Office No. 4', 'Manila City Parole And Probation Office No. 5',
            'Manila City Parole And Probation Office No. 6', 'Marikina City Parole And Probation Office', 'Muntinlupa City Parole And Probation Office',
            'Paranaque City Parole And Probation Office', 'Pasay City Parole And Probation Office', 'Pasig City Parole And Probation Office',
            'Quezon City Parole And Probation Office No. 1', 'Quezon City Parole And Probation Office No. 2', 'Quezon City Parole And Probation Office No. 3',
            'Taguig City Parole And Probation Office', 'Valenzuela City Parole And Probation Office', 'Central Office HQ',
            'Davao City Parole And Probation Office No. 1', 'Davao City Parole And Probation Office No. 2', 'Davao City Parole And Probation Office No. 3',
            'Samal Island Parole And Probation Office', 'Tagum City Parole And Probation Office', 'Digos City Parole And Probation Office',
            'Davao Province Parole And Probation Office No. 1', 'Davao Province Parole And Probation Office No. 2', 'Compostela Valley Province Parole And Probation Office',
            'Davao Del Sur Parole And Probation Office No. 1', 'Davao Del Sur Parole And Probation Office No. 2', 'Davao Oriental Parole And Probation Office',
            'Angeles City Parole And Probation Office', 'Cabanatuan City Parole And Probation Office', 'Malolos City Parole And Probation Office',
            'Olongapo City Parole And Probation Office', 'San Jose City Parole And Probation Office', 'Tarlac City Parole And Probation Office',
            'Aurora Parole And Probation Office', 'Bataan Parole And Probation Office', 'Bulacan Parole And Probation Office No. 1',
            'Bulacan Parole And Probation Office No. 2', 'Nueva Ecija Parole And Probation Office', 'Pampanga Parole And Probation Office',
            'Tarlac Province Parole And Probation Office', 'Zambales Province Parole And Probation Office', 'Northern Samar Parole And Probation Office',
            'Island Garden City Of Samal Parole And Probation Office', 'Taguig/Pateros City Parole And Probation Office',
            'Basilan Parole and Probation Office', 'Panabo City Parole and Probation Office', 'Technical Services Division',
            'Davao Occidental Parole And Probation Office', 'Davao Penal Colony', 'Office 10',
        ];
    }
}
