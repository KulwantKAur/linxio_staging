<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190408092902 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE time_zone_id_seq CASCADE');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_client_timezone_time_zone_id');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404553701B297 FOREIGN KEY (timezone) REFERENCES time_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_zone ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE users ADD blocking_message TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD driver_id VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE time_zone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE users DROP driver_id');
        $this->addSql('ALTER TABLE users DROP blocking_message');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C74404553701B297');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_client_timezone_time_zone_id FOREIGN KEY (timezone) REFERENCES time_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE SEQUENCE time_zone_id_seq');
        $this->addSql('SELECT setval(\'time_zone_id_seq\', (SELECT MAX(id) FROM time_zone))');
        $this->addSql('ALTER TABLE time_zone ALTER id SET DEFAULT nextval(\'time_zone_id_seq\')');
    }
}
