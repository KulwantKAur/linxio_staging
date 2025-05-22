<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190906114656 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $baseImei = 800000000000000;

        for ($currentImei = $baseImei; $currentImei < $baseImei + 100; $currentImei++) {
            $newImei = 7 . substr(strval($currentImei), 1);
            $this->addSql('UPDATE device SET imei = ' . $newImei . ' WHERE imei = \''. strval($currentImei) .'\';');
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $baseImei = 700000000000000;

        for ($currentImei = $baseImei; $currentImei < $baseImei + 100; $currentImei++) {
            $newImei = 8 . substr(strval($currentImei), 1);
            $this->addSql('UPDATE device SET imei = ' . $newImei . ' WHERE imei = \''. strval($currentImei) .'\';');
        }
    }
}
