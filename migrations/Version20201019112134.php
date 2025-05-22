<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201019112134 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification_message DROP CONSTRAINT FK_A3A3BAC8D8FE2AD4');
        $this->addSql('ALTER TABLE notification_message ADD CONSTRAINT FK_A3A3BAC8D8FE2AD4 FOREIGN KEY (event_log_id) REFERENCES event_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE notification_message DROP CONSTRAINT fk_a3a3bac8d8fe2ad4');
        $this->addSql('ALTER TABLE notification_message ADD CONSTRAINT fk_a3a3bac8d8fe2ad4 FOREIGN KEY (event_log_id) REFERENCES event_log (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
