<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190523090630 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device ADD created_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE device ADD updated_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_92FB68EDE12AB56 ON device (created_by)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_92FB68E16FE72E1 ON device (updated_by)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E16FE72E1');
        $this->addSql('DROP INDEX IDX_92FB68E16FE72E1');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68EDE12AB56');
        $this->addSql('DROP INDEX IDX_92FB68EDE12AB56');
        $this->addSql('ALTER TABLE device DROP created_by');
        $this->addSql('ALTER TABLE device DROP created_at');
        $this->addSql('ALTER TABLE device DROP updated_by');
    }
}
