<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609105630 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE speeding ALTER distance TYPE BIGINT');
        $this->addSql('ALTER TABLE speeding ALTER distance DROP DEFAULT');
        $this->addSql('ALTER TABLE route ALTER distance TYPE BIGINT');
        $this->addSql('ALTER TABLE route ALTER distance DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE route ALTER distance TYPE INT');
        $this->addSql('ALTER TABLE route ALTER distance DROP DEFAULT');
        $this->addSql('ALTER TABLE speeding ALTER distance TYPE INT');
        $this->addSql('ALTER TABLE speeding ALTER distance DROP DEFAULT');
    }
}
