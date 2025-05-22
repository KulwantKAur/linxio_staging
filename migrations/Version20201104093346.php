<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201104093346 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX route_start_coordinates_index ON route USING GIST (start_coordinates);');
        $this->addSql('CREATE INDEX route_finish_coordinates_index ON route USING GIST (finish_coordinates);');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}
