<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Setting;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210163807 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql($this->getSqlToUpdateSetting(Setting::ECO_SPEED));
        $this->addSql($this->getSqlToUpdateSetting(Setting::EXCESSIVE_IDLING));
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql($this->getSqlToRevertSetting(Setting::ECO_SPEED));
        $this->addSql($this->getSqlToRevertSetting(Setting::EXCESSIVE_IDLING));
    }

    /**
     * @param string $settingName
     * @return string
     */
    private function getSqlToUpdateSetting(string $settingName)
    {
        return 'UPDATE setting SET value = s_sub.new_value
            FROM (SELECT 
                s.id AS id, 
                s.name AS name, 
                json_build_object(\'value\',json_extract_path(s.value, \'value\')) AS new_value
                FROM setting s
                WHERE s.name = \''. $settingName .'\'
                ORDER BY s.id
            ) AS s_sub
            WHERE setting.name = \''. $settingName .'\'
            AND s_sub.id = setting.id';
    }

    /**
     * @param string $settingName
     * @return string
     */
    private function getSqlToRevertSetting(string $settingName)
    {
        return 'UPDATE setting SET value = s_sub.new_value
            FROM (SELECT 
                s.id AS id, 
                s.name AS name, 
                json_build_object(\'enable\',true,\'value\',json_extract_path(s.value, \'value\')) AS new_value
                FROM setting s
                WHERE s.name = \''. $settingName .'\'
                ORDER BY s.id
            ) AS s_sub
            WHERE setting.name = \''. $settingName .'\'
            AND s_sub.id = setting.id';
    }
}
