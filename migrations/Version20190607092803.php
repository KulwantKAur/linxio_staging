<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190607092803 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_c74404553701b297');
        $this->addSql('ALTER TABLE client DROP timezone');
        $this->addSql('ALTER TABLE setting ADD user_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B898A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9F74B898A76ED395 ON setting (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client ADD timezone INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_c74404553701b297 FOREIGN KEY (timezone) REFERENCES time_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE setting DROP CONSTRAINT FK_9F74B898A76ED395');
        $this->addSql('DROP INDEX IDX_9F74B898A76ED395');
        $this->addSql('ALTER TABLE setting DROP user_id');
    }
}
