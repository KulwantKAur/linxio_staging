<?php

namespace App\Service\Device\Factory;

use App\Entity\File;
use App\Service\Device\Mapper\File\Csv\DevicesVehiclesCsvFile;
use App\Service\Device\Mapper\File\Csv\DevicesVehiclesDriversCsvFile;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FileMapperFactory
 */
class FileMapperFactory
{

    /**
     * @param File $fileEntity
     * @param $translator
     * @return \Closure|mixed
     * @throws \Exception
     */
    public static function getFieldsForMapping(File $fileEntity, TranslatorInterface $translator)
    {
        $csvFileMapper = [(new DevicesVehiclesCsvFile($translator))];
        $extensionsMapper = [File::EXTENSION_CSV => $csvFileMapper];

        return $extensionsMapper[$fileEntity->getExtension()]
            ?? static function () {
                throw new \Exception('Mapper not defined for file upload!');
            };
    }

    public static function getFieldsForMapping2(File $fileEntity, TranslatorInterface $translator)
    {
        $csvFileMapper = [(new DevicesVehiclesDriversCsvFile($translator))];
        $extensionsMapper = [File::EXTENSION_CSV => $csvFileMapper];

        return $extensionsMapper[$fileEntity->getExtension()]
            ?? static function () {
                throw new \Exception('Mapper not defined for file upload!');
            };
    }
}
