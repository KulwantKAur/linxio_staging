<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200410063803 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX uniq_ca2b62548cde5729ab48c8e8');
        $this->addSql('ALTER TABLE notification_scope_type ADD category VARCHAR(255) DEFAULT \'general\' NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CA2B62548CDE5729AB48C8E864C19C1 ON notification_scope_type (type, sub_type, category)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_CA2B62548CDE5729AB48C8E864C19C1');
        $this->addSql('ALTER TABLE notification_scope_type DROP category');
        $this->addSql('CREATE UNIQUE INDEX uniq_ca2b62548cde5729ab48c8e8 ON notification_scope_type (type, sub_type)');
    }
}
