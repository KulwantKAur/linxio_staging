<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210614125622 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE reseller ADD favicon_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD theme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD client_default_theme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD product_name VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD support_msg TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD support_email VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_18015899D78119FD FOREIGN KEY (favicon_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_1801589959027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_180158997B8FC132 FOREIGN KEY (client_default_theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18015899D78119FD ON reseller (favicon_id)');
        $this->addSql('CREATE INDEX IDX_1801589959027487 ON reseller (theme_id)');
        $this->addSql('CREATE INDEX IDX_180158997B8FC132 ON reseller (client_default_theme_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE vehicle ALTER type_id DROP NOT NULL');
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT FK_18015899D78119FD');
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT FK_1801589959027487');
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT FK_180158997B8FC132');
        $this->addSql('DROP INDEX UNIQ_18015899D78119FD');
        $this->addSql('DROP INDEX IDX_1801589959027487');
        $this->addSql('DROP INDEX IDX_180158997B8FC132');
        $this->addSql('ALTER TABLE reseller DROP favicon_id');
        $this->addSql('ALTER TABLE reseller DROP theme_id');
        $this->addSql('ALTER TABLE reseller DROP client_default_theme_id');
        $this->addSql('ALTER TABLE reseller DROP product_name');
        $this->addSql('ALTER TABLE reseller DROP support_msg');
        $this->addSql('ALTER TABLE reseller DROP support_email');
    }
}
