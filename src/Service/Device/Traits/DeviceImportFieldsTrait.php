<?php

namespace App\Service\Device\Traits;

use App\Entity\Client;
use App\Entity\Reseller;
use App\Entity\VehicleType;

trait DeviceImportFieldsTrait
{
    private bool $withReinstall = false;
    private bool $ignoreElasticSearch = false;
    public bool $forSave = false;

    public function validateImportFields(array $fields)
    {
        $errors = [];

        $vendor = $fields['deviceVendor'] ?? null;
        if (!$vendor) {
//            $errors[] = 'Device Vendor - ' . $this->translator->trans('validation.errors.field.required');
        } else {
            $vendor = $this->findVendor($vendor);
            if (!$vendor) {
                $errors[] = $this->translator->trans('entities.device.vendor');
            } else {
                $model = $fields['deviceModel'] ?? null;
                if (!($model ?? null)) {
                    $errors[] = 'Device Model - ' . $this->translator->trans('validation.errors.field.required');
                } else {
                    $model = $this->findModel($vendor, $model);
                    if (!$model) {
                        $errors[] = $this->translator->trans('entities.device.model');
                    }
                }
            }
        }
        $imei = $fields['deviceImei'] ?? null;

        if ($imei) {
            $device = $this->findDevice($imei);
            if(!$device && !$vendor){
                $errors[] = $this->translator->trans('entities.device.not_found');
            }
            if ($device
                && $device->getVehicle()
                && isset($fields['vehicleRegNo'])
                && strtolower($device->getVehicle()->getRegNo()) !== strtolower((string)$fields['vehicleRegNo'])
                && !$this->withReinstall
            ) {
                $errors[] = $this->translator->trans('entities.device.installed_different_vehicle');
            }
        } else {
//            $errors[] = 'Device IMEI - ' . $this->translator->trans('validation.errors.field.required');
        }

        $phone = $fields['devicePhone'] ?? null;
        if (!($phone)) {
//            $errors[] = 'Device Phone - ' . $this->translator->trans('validation.errors.field.required');
        }

        $vehicleRegNo = $fields['vehicleRegNo'] ?? null;
        $vehicle = $this->findVehicle($vehicleRegNo);
        $clientId = $fields['clientId'] ?? null;

        if ($clientId) {
            if (is_numeric($clientId) && (int)$clientId == $clientId) {
                $client = $this->em->getRepository(Client::class)->find($clientId);
            }

            if (!isset($client) || !$client) {
                $errors[] = $this->translator->trans('entities.client.unknownClientId');
            } elseif (
                $vehicle && $vehicle->getTeam()->isClientTeam() && $vehicle->getTeam()->getClientId() !== (int)$clientId
            ) {
                $errors[] = $this->translator->trans('entities.vehicle.belongsAnotherClient');
            }
        }

        $resellerId = $fields['resellerId'] ?? null;

        if ($resellerId) {
            if (is_numeric($resellerId) && (int)$resellerId == $resellerId) {
                $reseller = $this->em->getRepository(Reseller::class)->find($resellerId);
            }

            if (!isset($reseller) || !$reseller) {
                $errors[] = $this->translator->trans('entities.reseller.unknownResellerId');
            }
        }

        if ($vehicleRegNo && !$vehicle && (!($fields['vehicleType'] ?? null) || !($fields['vehicleTitle'] ?? null))) {
            $errors[] = $this->translator->trans('entities.vehicle.regNoWithouDetails');
        } elseif (
            $vehicle && $vehicle->getDevice()
            && $vehicle->getDevice()
            && strtolower($vehicle->getDevice()->getImei()) !== strtolower($imei)
            && !$this->withReinstall
        ) {
            $errors[] = $this->translator->trans('entities.vehicle.vehicle_installed');
        }

        $vehicleType = $fields['vehicleType'] ?? null;
        if ($vehicleType) {
            $vehicleTypeIsset = $this->em->getRepository(VehicleType::class)
                ->getVehiclesTypeByName(strtolower($vehicleType));
            if (!$vehicleTypeIsset) {
                $errors[] = $this->translator->trans('entities.vehicle.type');
            }
        }

        return $errors;
    }
}