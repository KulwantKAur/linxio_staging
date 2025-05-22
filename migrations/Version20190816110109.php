<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190816110109 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE event_log ADD event_source_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log DROP event_by');
        $this->addSql('ALTER TABLE event_log ALTER triggered_by DROP NOT NULL');
        $this->addSql('ALTER TABLE event_log ALTER triggered_details DROP NOT NULL');
        $this->addSql('ALTER TABLE event_log ALTER event_details TYPE JSON USING event_details::json');
        $this->addSql('ALTER TABLE event_log ALTER event_details DROP DEFAULT');
        $this->addSql('ALTER TABLE event_log ALTER event_details DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN event_log.event_details IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE event_log DROP team_data');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE event_log ADD event_by VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_log DROP event_source_type');
        $this->addSql('ALTER TABLE event_log ALTER triggered_by SET NOT NULL');
        $this->addSql('ALTER TABLE event_log ALTER triggered_details SET NOT NULL');
        $this->addSql('ALTER TABLE event_log ALTER event_details TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE event_log ALTER event_details DROP DEFAULT');
        $this->addSql('ALTER TABLE event_log ALTER event_details SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN event_log.event_details IS NULL');
        $this->addSql('ALTER TABLE event_log ADD team_data VARCHAR(255) DEFAULT NULL');
    }
}
