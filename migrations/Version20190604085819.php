<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190604085819 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE team_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE team (id INT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE admin_team_permission (user_id BIGINT NOT NULL, team_id INT NOT NULL, PRIMARY KEY(user_id, team_id))');
        $this->addSql('CREATE INDEX IDX_E2400C88A76ED395 ON admin_team_permission (user_id)');
        $this->addSql('CREATE INDEX IDX_E2400C88296CD8AE ON admin_team_permission (team_id)');
        $this->addSql('ALTER TABLE admin_team_permission ADD CONSTRAINT FK_E2400C88A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admin_team_permission ADD CONSTRAINT FK_E2400C88296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE admin_client_permission');
        $this->addSql('ALTER TABLE client ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455296CD8AE ON client (team_id)');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT fk_1b80e48619eb6921');
        $this->addSql('DROP INDEX idx_1b80e48619eb6921');
        $this->addSql('ALTER TABLE vehicle ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle DROP client_id');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B80E486296CD8AE ON vehicle (team_id)');
        $this->addSql('ALTER TABLE vehicle_group DROP CONSTRAINT fk_f6fc42919eb6921');
        $this->addSql('DROP INDEX idx_f6fc42919eb6921');
        $this->addSql('ALTER TABLE vehicle_group ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_group DROP client_id');
        $this->addSql('ALTER TABLE vehicle_group ADD CONSTRAINT FK_F6FC429296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F6FC429296CD8AE ON vehicle_group (team_id)');
        $this->addSql('ALTER TABLE setting DROP CONSTRAINT fk_9f74b89819eb6921');
        $this->addSql('DROP INDEX idx_9f74b89819eb6921');
        $this->addSql('ALTER TABLE setting ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE setting DROP client_id');
        $this->addSql('ALTER TABLE setting DROP team');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B898296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9F74B898296CD8AE ON setting (team_id)');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT fk_92fb68e19eb6921');
        $this->addSql('DROP INDEX idx_92fb68e19eb6921');
        $this->addSql('ALTER TABLE device ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device DROP client_id');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_92FB68E296CD8AE ON device (team_id)');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT fk_1483a5e919eb6921');
        $this->addSql('DROP INDEX idx_1483a5e919eb6921');
        $this->addSql('ALTER TABLE users ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users DROP client_id');
        $this->addSql('ALTER TABLE users DROP team_type');
        $this->addSql('ALTER TABLE users RENAME COLUMN all_clients_permissions TO all_teams_permissions');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1483A5E9296CD8AE ON users (team_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455296CD8AE');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E486296CD8AE');
        $this->addSql('ALTER TABLE vehicle_group DROP CONSTRAINT FK_F6FC429296CD8AE');
        $this->addSql('ALTER TABLE setting DROP CONSTRAINT FK_9F74B898296CD8AE');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E296CD8AE');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9296CD8AE');
        $this->addSql('ALTER TABLE admin_team_permission DROP CONSTRAINT FK_E2400C88296CD8AE');
        $this->addSql('DROP SEQUENCE team_id_seq CASCADE');
        $this->addSql('CREATE TABLE admin_client_permission (user_id BIGINT NOT NULL, client_id BIGINT NOT NULL, PRIMARY KEY(user_id, client_id))');
        $this->addSql('CREATE INDEX idx_c89e5843a76ed395 ON admin_client_permission (user_id)');
        $this->addSql('CREATE INDEX idx_c89e584319eb6921 ON admin_client_permission (client_id)');
        $this->addSql('ALTER TABLE admin_client_permission ADD CONSTRAINT fk_3107a4eaa76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admin_client_permission ADD CONSTRAINT fk_3107a4ea19eb6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE admin_team_permission');
        $this->addSql('DROP INDEX IDX_1483A5E9296CD8AE');
        $this->addSql('ALTER TABLE users ADD client_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD team_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users DROP team_id');
        $this->addSql('ALTER TABLE users RENAME COLUMN all_teams_permissions TO all_clients_permissions');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_1483a5e919eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1483a5e919eb6921 ON users (client_id)');
        $this->addSql('DROP INDEX UNIQ_C7440455296CD8AE');
        $this->addSql('ALTER TABLE client DROP team_id');
        $this->addSql('DROP INDEX IDX_9F74B898296CD8AE');
        $this->addSql('ALTER TABLE setting ADD client_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE setting ADD team VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE setting DROP team_id');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT fk_9f74b89819eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9f74b89819eb6921 ON setting (client_id)');
        $this->addSql('DROP INDEX IDX_F6FC429296CD8AE');
        $this->addSql('ALTER TABLE vehicle_group ADD client_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_group DROP team_id');
        $this->addSql('ALTER TABLE vehicle_group ADD CONSTRAINT fk_f6fc42919eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f6fc42919eb6921 ON vehicle_group (client_id)');
        $this->addSql('DROP INDEX IDX_92FB68E296CD8AE');
        $this->addSql('ALTER TABLE device ADD client_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE device DROP team_id');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT fk_92fb68e19eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_92fb68e19eb6921 ON device (client_id)');
        $this->addSql('DROP INDEX IDX_1B80E486296CD8AE');
        $this->addSql('ALTER TABLE vehicle ADD client_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle DROP team_id');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT fk_1b80e48619eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1b80e48619eb6921 ON vehicle (client_id)');
    }
}
