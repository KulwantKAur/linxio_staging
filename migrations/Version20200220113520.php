<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200220113520 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE plan_role_permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE plan_role_permission (id INT NOT NULL, role_id INT DEFAULT NULL, plan_id INT DEFAULT NULL, permission_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F17CFF8D60322AC ON plan_role_permission (role_id)');
        $this->addSql('CREATE INDEX IDX_8F17CFF8E899029B ON plan_role_permission (plan_id)');
        $this->addSql('CREATE INDEX IDX_8F17CFF8FED90CCA ON plan_role_permission (permission_id)');
        $this->addSql('ALTER TABLE plan_role_permission ADD CONSTRAINT FK_8F17CFF8D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plan_role_permission ADD CONSTRAINT FK_8F17CFF8E899029B FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plan_role_permission ADD CONSTRAINT FK_8F17CFF8FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE plan_permission');
        $this->addSql('DROP TABLE role_permission');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE TABLE role_permission (role_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(role_id, permission_id))');
        $this->addSql('CREATE INDEX idx_6f7df886d60322ac ON role_permission (role_id)');
        $this->addSql('CREATE INDEX idx_6f7df886fed90cca ON role_permission (permission_id)');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT fk_6f7df886fed90cca FOREIGN KEY (permission_id) REFERENCES permission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT fk_6f7df886d60322ac FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP SEQUENCE plan_role_permission_id_seq CASCADE');
        $this->addSql('CREATE TABLE plan_permission (plan_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(plan_id, permission_id))');
        $this->addSql('CREATE INDEX idx_8d24a8bcfed90cca ON plan_permission (permission_id)');
        $this->addSql('CREATE INDEX idx_8d24a8bce899029b ON plan_permission (plan_id)');
        $this->addSql('ALTER TABLE plan_permission ADD CONSTRAINT fk_8d24a8bcfed90cca FOREIGN KEY (permission_id) REFERENCES permission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plan_permission ADD CONSTRAINT fk_8d24a8bce899029b FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE plan_role_permission');

    }
}
