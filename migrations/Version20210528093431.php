<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210528093431 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE note ADD reseller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1491E6A19D FOREIGN KEY (reseller_id) REFERENCES reseller (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CFBDFA1491E6A19D ON note (reseller_id)');
        $this->addSql('ALTER TABLE reseller ADD manager_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD legal_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD billing_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD tax_nr VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller RENAME COLUMN address TO legal_name');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_18015899783E3463 FOREIGN KEY (manager_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_18015899783E3463 ON reseller (manager_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE note DROP CONSTRAINT FK_CFBDFA1491E6A19D');
        $this->addSql('DROP INDEX IDX_CFBDFA1491E6A19D');
        $this->addSql('ALTER TABLE note DROP reseller_id');
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT FK_18015899783E3463');
        $this->addSql('DROP INDEX IDX_18015899783E3463');
        $this->addSql('ALTER TABLE reseller ADD address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller DROP manager_id');
        $this->addSql('ALTER TABLE reseller DROP legal_name');
        $this->addSql('ALTER TABLE reseller DROP legal_address');
        $this->addSql('ALTER TABLE reseller DROP billing_address');
        $this->addSql('ALTER TABLE reseller DROP tax_nr');
    }
}
