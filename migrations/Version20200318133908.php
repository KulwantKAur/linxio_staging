<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200318133908 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE service_record_file ALTER file_id TYPE BIGINT');
        $this->addSql('ALTER TABLE service_record_file ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER picture_id TYPE BIGINT');
        $this->addSql('ALTER TABLE users ALTER picture_id DROP DEFAULT');
        $this->addSql('ALTER TABLE vehicle ALTER picture_id TYPE BIGINT');
        $this->addSql('ALTER TABLE vehicle ALTER picture_id DROP DEFAULT');
        $this->addSql('ALTER TABLE document_record_file ALTER file_id TYPE BIGINT');
        $this->addSql('ALTER TABLE document_record_file ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE fuel_card ALTER file_id TYPE BIGINT');
        $this->addSql('ALTER TABLE fuel_card ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE inspection_form_data_value ALTER file_id TYPE BIGINT');
        $this->addSql('ALTER TABLE inspection_form_data_value ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE inspection_form_file ALTER file_id TYPE BIGINT');
        $this->addSql('ALTER TABLE inspection_form_file ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE device_installation_file ALTER file_id TYPE BIGINT');
        $this->addSql('ALTER TABLE device_installation_file ALTER file_id DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users ALTER picture_id TYPE INT');
        $this->addSql('ALTER TABLE users ALTER picture_id DROP DEFAULT');
        $this->addSql('ALTER TABLE vehicle ALTER picture_id TYPE INT');
        $this->addSql('ALTER TABLE vehicle ALTER picture_id DROP DEFAULT');
        $this->addSql('ALTER TABLE service_record_file ALTER file_id TYPE INT');
        $this->addSql('ALTER TABLE service_record_file ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE document_record_file ALTER file_id TYPE INT');
        $this->addSql('ALTER TABLE document_record_file ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE device_installation_file ALTER file_id TYPE INT');
        $this->addSql('ALTER TABLE device_installation_file ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE fuel_card ALTER file_id TYPE INT');
        $this->addSql('ALTER TABLE fuel_card ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE inspection_form_file ALTER file_id TYPE INT');
        $this->addSql('ALTER TABLE inspection_form_file ALTER file_id DROP DEFAULT');
        $this->addSql('ALTER TABLE inspection_form_data_value ALTER file_id TYPE INT');
        $this->addSql('ALTER TABLE inspection_form_data_value ALTER file_id DROP DEFAULT');
    }
}
