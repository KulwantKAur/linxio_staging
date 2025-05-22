<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220312113429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reseller ADD sales_manager_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD CONSTRAINT FK_180158997702830F FOREIGN KEY (sales_manager_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_180158997702830F ON reseller (sales_manager_id)');
        $this->addSql('UPDATE role SET name = \'reseller_sales_manager\' where name = \'reseller_manager\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reseller DROP CONSTRAINT FK_180158997702830F');
        $this->addSql('DROP INDEX IDX_180158997702830F');
        $this->addSql('ALTER TABLE reseller DROP sales_manager_id');
        $this->addSql('UPDATE role SET name = \'reseller_manager\' where name = \'reseller_sales_manager\'');
    }
}
