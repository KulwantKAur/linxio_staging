<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190614112448 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E486296CD8AE');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_group DROP CONSTRAINT FK_F6FC429296CD8AE');
        $this->addSql('ALTER TABLE vehicle_group ADD CONSTRAINT FK_F6FC429296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE setting DROP CONSTRAINT FK_9F74B898D60322AC');
        $this->addSql('ALTER TABLE setting DROP CONSTRAINT FK_9F74B898296CD8AE');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B898D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B898296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E296CD8AE');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9296CD8AE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users DROP CONSTRAINT fk_1483a5e9d60322ac');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT fk_1483a5e9296cd8ae');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_1483a5e9d60322ac FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_1483a5e9296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_group DROP CONSTRAINT fk_f6fc429296cd8ae');
        $this->addSql('ALTER TABLE vehicle_group ADD CONSTRAINT fk_f6fc429296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT fk_92fb68e296cd8ae');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT fk_92fb68e296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT fk_1b80e486296cd8ae');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT fk_1b80e486296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE setting DROP CONSTRAINT fk_9f74b898d60322ac');
        $this->addSql('ALTER TABLE setting DROP CONSTRAINT fk_9f74b898296cd8ae');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT fk_9f74b898d60322ac FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT fk_9f74b898296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
