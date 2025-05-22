<?php

namespace App\Service\FuelStation\Factory;

use App\Entity\File;
use App\Service\FuelStation\Mapper\File\Csv\FuelStationCsvFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileMapperFactory
{
    public static function getFieldsForMapping(File $fileEntity, TranslatorInterface $translator)
    {
        $csvFileMapper = [(new FuelStationCsvFile($translator))];
        $extensionsMapper = [File::EXTENSION_CSV => $csvFileMapper];

        return $extensionsMapper[$fileEntity->getExtension()]
            ?? static function () {
                throw new \Exception('Mapper not defined for file upload!');
            };
    }
}
