<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190617124201 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE device_vendor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE device_model_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device_vendor (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE device_model (id INT NOT NULL, vendor_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, protocol VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_111092BEF603EE73 ON device_model (vendor_id)');
        $this->addSql('ALTER TABLE device_model ADD CONSTRAINT FK_111092BEF603EE73 FOREIGN KEY (vendor_id) REFERENCES device_vendor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device ADD model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device DROP vendor');
        $this->addSql('ALTER TABLE device DROP model');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E7975B7E7 FOREIGN KEY (model_id) REFERENCES device_model (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_92FB68E7975B7E7 ON device (model_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_model DROP CONSTRAINT FK_111092BEF603EE73');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E7975B7E7');
        $this->addSql('DROP SEQUENCE device_vendor_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE device_model_id_seq CASCADE');
        $this->addSql('DROP TABLE device_vendor');
        $this->addSql('DROP TABLE device_model');
        $this->addSql('DROP INDEX IDX_92FB68E7975B7E7');
        $this->addSql('ALTER TABLE device ADD vendor VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE device ADD model VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE device DROP model_id');
    }
}
