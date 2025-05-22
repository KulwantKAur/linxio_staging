<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190926122151 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE IF EXISTS tracker_sensor_id_seq');
        $this->addSql('CREATE SEQUENCE tracker_sensor_id_seq');
        $this->addSql('SELECT setval(\'tracker_sensor_id_seq\', (SELECT MAX(id) FROM tracker_sensor))');
        $this->addSql('ALTER TABLE tracker_sensor ALTER id SET DEFAULT nextval(\'tracker_sensor_id_seq\')');

        $this->addSql('DROP SEQUENCE IF EXISTS tracker_history_id_seq');
        $this->addSql('CREATE SEQUENCE tracker_history_id_seq');
        $this->addSql('SELECT setval(\'tracker_history_id_seq\', (SELECT MAX(id) FROM tracker_history))');
        $this->addSql('ALTER TABLE tracker_history ALTER id SET DEFAULT nextval(\'tracker_history_id_seq\')');

        $this->addSql('DROP SEQUENCE IF EXISTS tracker_history_last_id_seq');
        $this->addSql('CREATE SEQUENCE tracker_history_last_id_seq');
        $this->addSql('SELECT setval(\'tracker_history_last_id_seq\', (SELECT MAX(id) FROM tracker_history_last))');
        $this->addSql('ALTER TABLE tracker_history_last ALTER id SET DEFAULT nextval(\'tracker_history_last_id_seq\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_sensor ALTER id DROP DEFAULT');

        $this->addSql('ALTER TABLE tracker_history ALTER id DROP DEFAULT');

        $this->addSql('ALTER TABLE tracker_history_last ALTER id DROP DEFAULT');
    }
}
