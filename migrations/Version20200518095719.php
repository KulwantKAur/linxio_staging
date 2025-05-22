<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200518095719 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE notification_mobile_device (id SERIAL NOT NULL, user_id BIGINT DEFAULT NULL, device_token VARCHAR(255) DEFAULT NULL, operation_system VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_20B9974DA76ED395 ON notification_mobile_device (user_id)');
        $this->addSql('ALTER TABLE notification_mobile_device ADD CONSTRAINT FK_20B9974DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mobile_device DROP CONSTRAINT FK_760B5D84A76ED395');
        $this->addSql('DROP INDEX IDX_760B5D84A76ED395');
        $this->addSql('ALTER TABLE mobile_device DROP user_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE notification_mobile_device');
    }
}
