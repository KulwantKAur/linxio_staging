<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190415071800 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE admin_client_permission (user_id BIGINT NOT NULL, client_id BIGINT NOT NULL, PRIMARY KEY(user_id, client_id))');
        $this->addSql('CREATE INDEX IDX_3107A4EAA76ED395 ON admin_client_permission (user_id)');
        $this->addSql('CREATE INDEX IDX_3107A4EA19EB6921 ON admin_client_permission (client_id)');
        $this->addSql('ALTER TABLE admin_client_permission ADD CONSTRAINT FK_3107A4EAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admin_client_permission ADD CONSTRAINT FK_3107A4EA19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD all_clients_permissions BOOLEAN DEFAULT \'true\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE admin_client_permission');
        $this->addSql('ALTER TABLE users DROP all_clients_permissions');
    }
}
