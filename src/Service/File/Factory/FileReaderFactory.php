<?php

namespace App\Service\File\Factory;

use App\Entity\File;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls as ReaderXls;

/**
 * Class FileReaderFactory
 */
class FileReaderFactory
{

    /**
     * @param File $fileEntity
     * @return ReaderCsv|ReaderXls|ReaderXlsx
     * @throws \Exception
     */
    public static function getInstance(File $fileEntity)
    {
        switch ($fileEntity->getExtension()) {
            case File::EXTENSION_CSV:
                $reader = new ReaderCsv();
                $reader->setDelimiter(',');
                $reader->setEnclosure('');
                break;
            case File::EXTENSION_TXT:
                $reader = new ReaderCsv();
                $reader->setDelimiter("\t");
                break;
            case File::EXTENSION_XLS:
                $reader = new ReaderXls();
                break;
            case File::EXTENSION_XLSX:
                $reader = new ReaderXlsx();
                break;
            default:
                throw new \Exception('Unsupported file format: ' . $fileEntity->getExtension());
        }
        $reader->setReadDataOnly(true);

        return $reader;
    }
}
