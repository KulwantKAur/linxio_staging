<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191015155411 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX uniq_a785295d94a4c7d4');
        $this->addSql('CREATE INDEX IDX_A785295D94A4C7D4 ON tracker_history_last (device_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A785295D545317D194A4C7D4 ON tracker_history_last (vehicle_id, device_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX IDX_A785295D94A4C7D4');
        $this->addSql('DROP INDEX UNIQ_A785295D545317D194A4C7D4');
        $this->addSql('CREATE UNIQUE INDEX uniq_a785295d94a4c7d4 ON tracker_history_last (device_id)');
    }
}
