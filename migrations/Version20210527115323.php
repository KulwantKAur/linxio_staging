<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210527115323 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX tracker_auth_unknown_imei_idx ON tracker_auth_unknown (imei)');
        $this->removeDuplicatesFromTrackerAuthUnknown();
        $this->addSql('DROP INDEX tracker_auth_unknown_imei_idx');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_56977B5B8179F8 ON tracker_auth_unknown (imei)');
        $this->addSql('ALTER TABLE tracker_auth_unknown ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE route_temp DROP points_count');
        $this->addSql('ALTER TABLE route_temp ALTER type TYPE VARCHAR(7)');
        $this->addSql('ALTER TABLE route DROP points_count');
        $this->addSql('ALTER TABLE route ALTER type TYPE VARCHAR(7)');
        $this->addSql('DROP INDEX tracker_auth_socket_id_idx');
        $this->addSql('DROP INDEX tracker_auth_imei_idx');
        $this->addSql('CREATE INDEX tracker_auth_imei_created_at_idx ON tracker_auth (imei, created_at)');
        $this->addSql('CREATE INDEX tracker_auth_socket_id_created_at_idx ON tracker_auth (socket_id, created_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX tracker_auth_imei_created_at_idx');
        $this->addSql('DROP INDEX tracker_auth_socket_id_created_at_idx');
        $this->addSql('CREATE INDEX tracker_auth_socket_id_idx ON tracker_auth (socket_id)');
        $this->addSql('CREATE INDEX tracker_auth_imei_idx ON tracker_auth (imei)');
        $this->addSql('ALTER TABLE route_temp ADD points_count DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE route_temp ALTER type TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE route ADD points_count DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE route ALTER type TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE tracker_auth_unknown DROP updated_at');
        $this->addSql('DROP INDEX UNIQ_56977B5B8179F8');
    }

    private function removeDuplicatesFromTrackerAuthUnknown()
    {
        $this->addSql('DELETE FROM tracker_auth_unknown tau  WHERE tau.id NOT IN (SELECT id FROM (SELECT DISTINCT ON (imei) * FROM tracker_auth_unknown) subq)');
    }
}
