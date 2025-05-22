<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191023065456 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE area_history DROP CONSTRAINT fk_246a19b6360893ec');
        $this->addSql('ALTER TABLE area_history DROP CONSTRAINT fk_246a19b6cba6adbe');
        $this->addSql('DROP INDEX idx_246a19b6360893ec');
        $this->addSql('DROP INDEX idx_246a19b6cba6adbe');
        $this->addSql('ALTER TABLE area_history DROP driver_arrived_id');
        $this->addSql('ALTER TABLE area_history DROP driver_departed_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE area_history ADD driver_arrived_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE area_history ADD driver_departed_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT fk_246a19b6360893ec FOREIGN KEY (driver_arrived_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE area_history ADD CONSTRAINT fk_246a19b6cba6adbe FOREIGN KEY (driver_departed_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_246a19b6360893ec ON area_history (driver_arrived_id)');
        $this->addSql('CREATE INDEX idx_246a19b6cba6adbe ON area_history (driver_departed_id)');
    }
}
