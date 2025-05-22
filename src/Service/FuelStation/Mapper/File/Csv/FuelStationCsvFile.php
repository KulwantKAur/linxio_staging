<?php

namespace App\Service\FuelStation\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class FuelStationCsvFile extends BaseFileMapper
{
    public const TRANSLATE_DOMAIN = 'fuelStation';

    /**
     * @return array
     */
    public function getInternalMappedFields()
    {
        return [
            'siteId' => $this->translator->trans('import.siteId', [], self::TRANSLATE_DOMAIN),
            'stationName' => $this->translator->trans('import.stationName', [], self::TRANSLATE_DOMAIN),
            'lng' => $this->translator->trans('import.lng', [], self::TRANSLATE_DOMAIN),
            'lat' => $this->translator->trans('import.lat', [], self::TRANSLATE_DOMAIN),
            'address' => $this->translator->trans('import.address', [], self::TRANSLATE_DOMAIN),
        ];
    }

    public function checkSkipLine(array $data)
    {
        return true;
    }

    public function specialPrepareFields(array $data)
    {

        return $data;
    }
}
