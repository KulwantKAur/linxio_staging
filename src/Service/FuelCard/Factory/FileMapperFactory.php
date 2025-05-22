<?php

namespace App\Service\FuelCard\Factory;

use App\Entity\File;
use App\Service\FuelCard\Mapper\File\Csv\BpInvoiceCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\CaltexTransactionDetailCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\ChevronCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\CireServicesCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\FleetCardCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\FuelTransactionCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\MotorpassV2File;
use App\Service\FuelCard\Mapper\File\Csv\ShellCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\ShellV2CsvFile;
use App\Service\FuelCard\Mapper\File\Csv\TransactionCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\TransactionNorthernBeachesCsvFile;
use App\Service\FuelCard\Mapper\File\Csv\TransactionV4CsvFile;
use App\Service\FuelCard\Mapper\File\Xlsx\CaltexTransactionDetailXlsxFile;
use App\Service\FuelCard\Mapper\File\Xlsx\CaltexXlsxFile;
use App\Service\FuelCard\Mapper\File\Xlsx\FuelCardV2XlsxFile;
use App\Service\FuelCard\Mapper\File\Xlsx\NswXlsxFile;
use App\Service\FuelCard\Mapper\File\Xlsx\ShellCardXlsxFile;
use App\Service\FuelCard\Mapper\File\Xlsx\ShellXlsFile;
use App\Service\FuelCard\Mapper\File\Txt\MPDataTxtFile;
use App\Service\FuelCard\Mapper\File\Txt\MotorpassTxtFile;
use App\Service\FuelCard\Mapper\File\Xlsx\TransactionV2XlsFile;
use App\Service\FuelCard\Mapper\File\Xlsx\TransactionV3XlsxFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileMapperFactory
{
    public static function getFieldsForMapping(File $fileEntity, TranslatorInterface $translator)
    {
        return [
                File::EXTENSION_CSV => [
                    (new CireServicesCsvFile($translator)),
                    (new TransactionV4CsvFile($translator)),
                    (new ChevronCsvFile($translator)),
                    (new BpInvoiceCsvFile($translator)),
                    (new TransactionCsvFile($translator)),
                    (new FleetCardCsvFile($translator)),
                    (new FuelTransactionCsvFile($translator)),
                    (new MotorpassV2File($translator)),
                    (new ShellCsvFile($translator)),
                    (new ShellV2CsvFile($translator)),
                    (new CaltexTransactionDetailCsvFile($translator)),
                    (new TransactionNorthernBeachesCsvFile($translator)),
                ],
                File::EXTENSION_XLS => [
                    (new ShellXlsFile($translator)),
                    (new TransactionV2XlsFile($translator)),
                ],
                File::EXTENSION_XLSX => [
                    (new CaltexXlsxFile($translator)),
                    (new CaltexTransactionDetailXlsxFile($translator)),
                    (new ShellCardXlsxFile($translator)),
                    (new TransactionV3XlsxFile($translator)),
                    (new FuelCardV2XlsxFile($translator)),
                    (new NswXlsxFile($translator)),
                ],
                File::EXTENSION_TXT => [
                    (new MotorpassTxtFile($translator)),
                    (new MPDataTxtFile($translator)),
                ],
            ][$fileEntity->getExtension()] ?? static function () {
                throw new \Exception('Mapper not defined for file upload!');
            };
    }
}
