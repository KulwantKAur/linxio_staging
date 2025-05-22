<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190820085121 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE device_installation_file (device_installation_id INT NOT NULL, file_id INT NOT NULL, PRIMARY KEY(device_installation_id, file_id))');
        $this->addSql('CREATE INDEX IDX_3636C8F4AB603759 ON device_installation_file (device_installation_id)');
        $this->addSql('CREATE INDEX IDX_3636C8F493CB796C ON device_installation_file (file_id)');
        $this->addSql('ALTER TABLE device_installation_file ADD CONSTRAINT FK_3636C8F4AB603759 FOREIGN KEY (device_installation_id) REFERENCES device_installation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_installation_file ADD CONSTRAINT FK_3636C8F493CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE device_installation_file');
    }
}
