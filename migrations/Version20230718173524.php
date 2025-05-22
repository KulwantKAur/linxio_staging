<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230718173524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744BB3BD4DA');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744B3FB3CB5');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744BB3BD4DA FOREIGN KEY (prepayment_id) REFERENCES invoice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744B3FB3CB5 FOREIGN KEY (previous_prepayment_id) REFERENCES invoice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT fk_90651744bb3bd4da');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT fk_90651744b3fb3cb5');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT fk_90651744bb3bd4da FOREIGN KEY (prepayment_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT fk_90651744b3fb3cb5 FOREIGN KEY (previous_prepayment_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
