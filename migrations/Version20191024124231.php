<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191024124231 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE time_zone ADD name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE time_zone DROP display_name');
        $this->addSql('ALTER TABLE time_zone DROP offset_value');
        $this->addSql('CREATE SEQUENCE time_zone_id_seq');
        $this->addSql('SELECT setval(\'time_zone_id_seq\', (SELECT MAX(id) FROM time_zone))');
        $this->addSql('ALTER TABLE time_zone ALTER id SET DEFAULT nextval(\'time_zone_id_seq\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE time_zone ADD display_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE time_zone ADD offset_value VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE time_zone DROP name');
        $this->addSql('ALTER TABLE time_zone ALTER id DROP DEFAULT');
    }
}
