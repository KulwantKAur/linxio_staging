<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190919091130 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle ADD device_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E48694A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B80E48694A4C7D4 ON vehicle (device_id)');
        $this->addSql('DROP INDEX idx_92fb68e545317d1');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92FB68E545317D1 ON device (vehicle_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E48694A4C7D4');
        $this->addSql('DROP INDEX UNIQ_1B80E48694A4C7D4');
        $this->addSql('ALTER TABLE vehicle DROP device_id');
        $this->addSql('DROP INDEX UNIQ_92FB68E545317D1');
        $this->addSql('CREATE INDEX idx_92fb68e545317d1 ON device (vehicle_id)');
    }
}
