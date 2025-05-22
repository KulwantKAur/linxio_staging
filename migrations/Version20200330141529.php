<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200330141529 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification ADD additional_params JSON DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN notification.additional_params IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE notification_event ADD additional_settings JSON DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN notification_event.additional_settings IS \'(DC2Type:json_array)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification DROP additional_params');
        $this->addSql('ALTER TABLE notification_event DROP additional_settings');
    }
}
