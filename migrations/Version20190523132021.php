<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190523132021 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle ADD created_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD updated_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE vehicle ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E48616FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B80E486DE12AB56 ON vehicle (created_by)');
        $this->addSql('CREATE INDEX IDX_1B80E48616FE72E1 ON vehicle (updated_by)');
        $this->addSql('ALTER TABLE vehicle ADD status VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle DROP status');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E486DE12AB56');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E48616FE72E1');
        $this->addSql('DROP INDEX IDX_1B80E486DE12AB56');
        $this->addSql('DROP INDEX IDX_1B80E48616FE72E1');
        $this->addSql('ALTER TABLE vehicle DROP created_by');
        $this->addSql('ALTER TABLE vehicle DROP updated_by');
        $this->addSql('ALTER TABLE vehicle DROP created_at');
        $this->addSql('ALTER TABLE vehicle DROP updated_at');
    }
}
