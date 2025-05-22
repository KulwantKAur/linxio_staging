<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011093144 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE streamax_integration (id SERIAL NOT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, url TEXT NOT NULL, signature TEXT NOT NULL, tenant_id VARCHAR(255) NOT NULL, secret TEXT DEFAULT NULL, status VARCHAR(255) DEFAULT \'enabled\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B55296A79033212A ON streamax_integration (tenant_id)');
        $this->addSql('CREATE INDEX IDX_B55296A7296CD8AE ON streamax_integration (team_id)');
        $this->addSql('ALTER TABLE streamax_integration ADD CONSTRAINT FK_B55296A7296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device ADD streamax_integration_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E18089324 FOREIGN KEY (streamax_integration_id) REFERENCES streamax_integration (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_92FB68E18089324 ON device (streamax_integration_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E18089324');
        $this->addSql('DROP INDEX IDX_92FB68E18089324');
        $this->addSql('ALTER TABLE device DROP streamax_integration_id');
        $this->addSql('DROP TABLE streamax_integration');
    }
}
