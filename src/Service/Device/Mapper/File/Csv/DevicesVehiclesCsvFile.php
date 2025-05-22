<?php

namespace App\Service\Device\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class DevicesVehiclesCsvFile extends BaseFileMapper
{
    /**
     * @return array
     */
    public function getInternalMappedFields()
    {
        return [
            'deviceVendor' => $this->translator->trans('import.devicesVehicles.deviceVendor'),
            'deviceModel' => $this->translator->trans('import.devicesVehicles.deviceModel'),
            'deviceImei' => $this->translator->trans('import.devicesVehicles.deviceImei'),
            'deviceImsi' => $this->translator->trans('import.devicesVehicles.deviceImsi'),
            'devicePhone' => $this->translator->trans('import.devicesVehicles.devicePhone'),
            'clientId' => $this->translator->trans('import.devicesVehicles.clientId'),
            'vehicleTitle' => $this->translator->trans('import.devicesVehicles.vehicleTitle'),
            'vehicleRegNo' => $this->translator->trans('import.devicesVehicles.vehicleRegNo'),
            'vehicleMake' => $this->translator->trans('import.devicesVehicles.vehicleMake'),
            'vehicleModel' => $this->translator->trans('import.devicesVehicles.vehicleModel'),
            'vehicleType' => $this->translator->trans('import.devicesVehicles.vehicleType'),
            'vehicleYear' => $this->translator->trans('import.devicesVehicles.vehicleYear'),
            'expDate' => $this->translator->trans('import.devicesVehicles.expDate'),
            'contractId' => $this->translator->trans('import.devicesVehicles.contractId'),
            'ownership' => $this->translator->trans('import.devicesVehicles.ownership'),
            'contractStart' => $this->translator->trans('import.devicesVehicles.contractStart'),
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function checkSkipLine(array $data)
    {
        return empty($data['deviceImei']) ? true : false;
    }

    public function specialPrepareFields(array $data)
    {
        $data['deviceImei'] = is_double($data['deviceImei'])
            ? number_format($data['deviceImei'], 0, '', '')
            : $data['deviceImei'];

        if (!empty($data['deviceImsi']) && !is_numeric($data['deviceImsi'])) {
            $data['deviceImsi'] = null;
        } else {
            $data['deviceImsi'] = is_double($data['deviceImsi'])
                ? number_format($data['deviceImsi'], 0, '', '')
                : $data['deviceImsi'];
        }

        if (!empty($data['vehicleRegNo'])) {
            if (is_numeric($data['vehicleRegNo'])) {
                $data['vehicleRegNo'] = is_double($data['vehicleRegNo'])
                    ? number_format($data['vehicleRegNo'], 0, '', '')
                    : $data['vehicleRegNo'];
            }
        } else {
            $data['vehicleRegNo'] = null;
        }

        if (empty($data['clientId']) || (!empty($data['clientId']) && !is_numeric($data['clientId']))) {
            $data['clientId'] = null;
        }

        if (empty($data['resellerId']) || (!empty($data['resellerId']) && !is_numeric($data['resellerId']))) {
            $data['resellerId'] = null;
        }

        return $data;
    }
}
