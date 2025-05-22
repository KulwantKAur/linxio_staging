<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Fixtures\VehicleTypes\InitVehicleTypesFixture;
use Doctrine\DBAL\Schema\Schema;
use App\Migrations\AbstractFixturesAwareMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210330125618 extends AbstractFixturesAwareMigration
{
    public function postUp(Schema $schema): void
    {
        // LoadMyData can be any fixture class
        $this->addFixture(new InitVehicleTypesFixture(
            $this->getContainer()->get('app.local_file_service'),
            $this->getContainer()->get('kernel')->getProjectDir().'/src/Fixtures'
        ));
//        $this->executeFixtures();
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('update vehicle set type_id = subquery.id from (select id, name from vehicle_type) as subquery where type IS NOT NULL and vehicle.type = subquery.name');
        $this->addSql('update vehicle set type_id = subquery.id from (select id, name from vehicle_type) as subquery where type IS NULL and subquery.name = \'Car\'');
        $this->addSql('ALTER TABLE vehicle DROP type');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE vehicle ADD type VARCHAR(255) DEFAULT NULL');
    }
}
