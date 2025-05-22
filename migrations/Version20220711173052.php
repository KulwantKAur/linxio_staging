<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220711173052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('
            CREATE TABLE invoice
            (
                id          BIGINT NOT NULL PRIMARY KEY,
                client_id   BIGINT NOT NULL CONSTRAINT invoice_client_id REFERENCES client,
                status      VARCHAR(255) NOT NULL,
                period_start DATE NOT NULL,
                period_end   DATE NOT NULL,
                due_at      TIMESTAMP(0) NOT NULL,
                created_at  TIMESTAMP(0) NOT NULL,
                created_by  BIGINT CONSTRAINT invoice_created_by REFERENCES users ON DELETE SET NULL,
                updated_at  TIMESTAMP(0) DEFAULT NULL::timestamp WITHOUT TIME ZONE,
                updated_by  BIGINT CONSTRAINT invoice_updated_by REFERENCES users ON DELETE SET NULL
            );
        ');

        $this->addSql('
            CREATE TABLE invoice_details
            (
                invoice_id  BIGINT NOT NULL CONSTRAINT id REFERENCES invoice ON DELETE CASCADE,
                key         VARCHAR(255) NOT NULL,
                amount      NUMERIC NOT NULL DEFAULT 0,
                price       NUMERIC NOT NULL DEFAULT 0,
                total       NUMERIC NOT NULL DEFAULT 0,
                constraint invoice_details_pk primary key (invoice_id, key)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE invoice_id_seq CASCADE');
        $this->addSql('DROP TABLE IF EXISTS invoice_details');
        $this->addSql('DROP TABLE IF EXISTS invoice');
    }
}
