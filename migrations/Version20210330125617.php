<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Fixtures\VehicleTypes\InitVehicleTypesFixture;
use Doctrine\DBAL\Schema\Schema;
use App\Migrations\AbstractFixturesAwareMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210330125617 extends AbstractFixturesAwareMigration
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

        $this->addSql('CREATE SEQUENCE vehicle_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vehicle_type (id INT NOT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FE436475296CD8AE ON vehicle_type (team_id)');
        $this->addSql('ALTER TABLE vehicle_type ADD CONSTRAINT FK_FE436475296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486C54C8C93 FOREIGN KEY (type_id) REFERENCES vehicle_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B80E486C54C8C93 ON vehicle (type_id)');
        $this->addSql('ALTER TABLE vehicle_type ADD default_picture_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_type ADD driving_picture_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_type ADD idling_picture_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_type ADD stopped_picture_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_type ADD CONSTRAINT FK_FE436475A666E9DC FOREIGN KEY (default_picture_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_type ADD CONSTRAINT FK_FE43647597B1DC83 FOREIGN KEY (driving_picture_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_type ADD CONSTRAINT FK_FE4364755DDAD042 FOREIGN KEY (idling_picture_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_type ADD CONSTRAINT FK_FE436475D6D02E9E FOREIGN KEY (stopped_picture_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE436475A666E9DC ON vehicle_type (default_picture_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE43647597B1DC83 ON vehicle_type (driving_picture_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE4364755DDAD042 ON vehicle_type (idling_picture_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE436475D6D02E9E ON vehicle_type (stopped_picture_id)');
        $this->addSql('ALTER TABLE vehicle_type ADD status VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_type ADD sort INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E486C54C8C93');
        $this->addSql('DROP SEQUENCE vehicle_type_id_seq CASCADE');
        $this->addSql('DROP TABLE vehicle_type');
        $this->addSql('DROP INDEX IDX_1B80E486C54C8C93');
        $this->addSql('ALTER TABLE vehicle DROP type_id');
        $this->addSql('ALTER TABLE vehicle_type DROP CONSTRAINT FK_FE436475A666E9DC');
        $this->addSql('ALTER TABLE vehicle_type DROP CONSTRAINT FK_FE43647597B1DC83');
        $this->addSql('ALTER TABLE vehicle_type DROP CONSTRAINT FK_FE4364755DDAD042');
        $this->addSql('ALTER TABLE vehicle_type DROP CONSTRAINT FK_FE436475D6D02E9E');
        $this->addSql('DROP INDEX UNIQ_FE436475A666E9DC');
        $this->addSql('DROP INDEX UNIQ_FE43647597B1DC83');
        $this->addSql('DROP INDEX UNIQ_FE4364755DDAD042');
        $this->addSql('DROP INDEX UNIQ_FE436475D6D02E9E');
        $this->addSql('ALTER TABLE vehicle_type DROP default_picture_id');
        $this->addSql('ALTER TABLE vehicle_type DROP driving_picture_id');
        $this->addSql('ALTER TABLE vehicle_type DROP idling_picture_id');
        $this->addSql('ALTER TABLE vehicle_type DROP stopped_picture_id');
        $this->addSql('ALTER TABLE vehicle_type DROP sort');
    }
}
