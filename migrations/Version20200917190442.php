<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200917190442 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('ALTER TABLE vehicle ADD make VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD make_model VARCHAR(255) DEFAULT NULL');
        $this->addSql(
            "update vehicle
    set make = CASE
                     WHEN position(' ' in model) = 0 THEN substring(model, 0)
                     ELSE
                         substring(model, 0, position(' ' in model))
    END,
    make_model = CASE
                     WHEN position(' ' in model) > 0 THEN substring(model, position(' ' in model) + 1)
        END");
        $this->addSql('ALTER TABLE vehicle DROP model');
        $this->addSql("CREATE FUNCTION model(vehicle) RETURNS text AS $$ SELECT $1.make || ' ' || $1.make_model; $$ LANGUAGE SQL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('DROP FUNCTION if exists model(vehicle)');
        $this->addSql('ALTER TABLE vehicle ADD model VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle DROP make');
        $this->addSql('ALTER TABLE vehicle DROP make_model');
    }
}
