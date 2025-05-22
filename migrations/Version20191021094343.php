<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021094343 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE area_history DROP CONSTRAINT fk_246a19b6c3423909');
        $this->addSql('DROP INDEX idx_246a19b6c3423909');
        $this->addSql('ALTER TABLE area_history ADD driver_departed_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE area_history RENAME COLUMN driver_id TO driver_arrived_id');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT FK_246A19B6360893EC FOREIGN KEY (driver_arrived_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT FK_246A19B6CBA6ADBE FOREIGN KEY (driver_departed_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_246A19B6360893EC ON area_history (driver_arrived_id)');
        $this->addSql('CREATE INDEX IDX_246A19B6CBA6ADBE ON area_history (driver_departed_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE area_history DROP CONSTRAINT FK_246A19B6360893EC');
        $this->addSql('ALTER TABLE area_history DROP CONSTRAINT FK_246A19B6CBA6ADBE');
        $this->addSql('DROP INDEX IDX_246A19B6360893EC');
        $this->addSql('DROP INDEX IDX_246A19B6CBA6ADBE');
        $this->addSql('ALTER TABLE area_history ADD driver_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE area_history DROP driver_arrived_id');
        $this->addSql('ALTER TABLE area_history DROP driver_departed_id');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT fk_246a19b6c3423909 FOREIGN KEY (driver_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_246a19b6c3423909 ON area_history (driver_id)');
    }
}
