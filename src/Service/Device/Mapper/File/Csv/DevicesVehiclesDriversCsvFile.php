<?php

namespace App\Service\Device\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class DevicesVehiclesDriversCsvFile extends BaseFileMapper
{
    public function getInternalMappedFields()
    {
        return [
            'deviceImei' => $this->translator->trans('import.devicesVehicles.deviceImei'),
            'clientId' => $this->translator->trans('import.devicesVehicles.clientId'),
            'vehicleTitle' => $this->translator->trans('import.devicesVehicles.vehicleTitle'),
            'vehicleRegNo' => $this->translator->trans('import.devicesVehicles.vehicleRegNo'),
            'vehicleModel' => $this->translator->trans('import.devicesVehicles.vehicleModel'),
            'vehicleMake' => $this->translator->trans('import.devicesVehicles.vehicleMake'),
            'vehicleType' => $this->translator->trans('import.devicesVehicles.vehicleType'),
            'vehicleDepot' => 'Veh_depot',
            'driverEmail' => 'Driver_email',
            'driverName' => 'Driver_name',
            'driverSurname' => 'Driver_surname',
            'driverPhone' => 'Driver_phone',
            'userGroup' => 'User_group',
        ];
    }

    public function checkSkipLine(array $data)
    {
        return empty($data['deviceImei']) ? true : false;
    }

    public function specialPrepareFields(array $data)
    {
        $data['deviceImei'] = is_double($data['deviceImei'])
            ? number_format($data['deviceImei'], 0, '', '')
            : $data['deviceImei'];

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
