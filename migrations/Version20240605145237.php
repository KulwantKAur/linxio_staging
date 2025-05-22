<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240605145237 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users ADD driver_fob_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9397D506A ON users (driver_fob_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX UNIQ_1483A5E9397D506A');
        $this->addSql('ALTER TABLE users DROP driver_fob_id');
    }
}