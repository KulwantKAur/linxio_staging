<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200513082353 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE users_vehicles (user_group_id INT NOT NULL, vehicle_id INT NOT NULL, PRIMARY KEY(user_group_id, vehicle_id))');
        $this->addSql('CREATE INDEX IDX_648F42201ED93D47 ON users_vehicles (user_group_id)');
        $this->addSql('CREATE INDEX IDX_648F4220545317D1 ON users_vehicles (vehicle_id)');
        $this->addSql('CREATE TABLE users_vehicles_groups (user_group_id INT NOT NULL, vehicle_group_id INT NOT NULL, PRIMARY KEY(user_group_id, vehicle_group_id))');
        $this->addSql('CREATE INDEX IDX_3810B1F1ED93D47 ON users_vehicles_groups (user_group_id)');
        $this->addSql('CREATE INDEX IDX_3810B1F2346D6D3 ON users_vehicles_groups (vehicle_group_id)');
        $this->addSql('CREATE TABLE users_vehicles_depots (user_group_id INT NOT NULL, depot_id INT NOT NULL, PRIMARY KEY(user_group_id, depot_id))');
        $this->addSql('CREATE INDEX IDX_2A7296481ED93D47 ON users_vehicles_depots (user_group_id)');
        $this->addSql('CREATE INDEX IDX_2A7296488510D4DE ON users_vehicles_depots (depot_id)');
        $this->addSql('ALTER TABLE users_vehicles ADD CONSTRAINT FK_648F42201ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_vehicles ADD CONSTRAINT FK_648F4220545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_vehicles_groups ADD CONSTRAINT FK_3810B1F1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_vehicles_groups ADD CONSTRAINT FK_3810B1F2346D6D3 FOREIGN KEY (vehicle_group_id) REFERENCES vehicle_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_vehicles_depots ADD CONSTRAINT FK_2A7296481ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_vehicles_depots ADD CONSTRAINT FK_2A7296488510D4DE FOREIGN KEY (depot_id) REFERENCES vehicle_depot (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE users_vehicles');
        $this->addSql('DROP TABLE users_vehicles_groups');
        $this->addSql('DROP TABLE users_vehicles_depots');
    }
}
