<?php

namespace App\Service\Vehicle;

use App\Entity\DriverHistory;
use App\Entity\Setting;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\VehicleType;
use App\Exceptions\ValidationException;
use App\Service\File\FileService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class VehicleServiceHelper
{
    /**
     * @param EntityManagerInterface $em
     * @param User $user
     * @param int $driverId
     * @param bool $isUsedSettingToVehiclesAccess
     * @return array|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private static function getDriverVehicleIdsBySetting(
        EntityManagerInterface $em,
        User $user,
        int $driverId,
        bool $isUsedSettingToVehiclesAccess
    ): ?array {
        if (!$isUsedSettingToVehiclesAccess) {
            return $em->getRepository(DriverHistory::class)->findVehicleIdsByDriver($driverId);
        }

        $vehicleIds = [];
        $driverVehiclesAccessTypeSetting = $user->getTeam()->getSettingByName(Setting::DRIVER_VEHICLES_ACCESS_TYPE);

        if ($driverVehiclesAccessTypeSetting) {
            switch ($driverVehiclesAccessTypeSetting->getValue()) {
                case Setting::DRIVER_VEHICLES_ACCESS_TYPE_CURRENT:
                    $vehicleId = $em->getRepository(DriverHistory::class)->findLastVehicleIdByDriver($driverId);
                    $vehicleIds = $vehicleId ? [$vehicleId] : [];
                    break;
                case Setting::DRIVER_VEHICLES_ACCESS_TYPE_ALL:
                    return null;
                default:
                    $vehicleIds = $em->getRepository(DriverHistory::class)->findVehicleIdsByDriver($driverId);
                    break;
            }
        }

        return $vehicleIds;
    }

    /**
     * @param array $params
     * @param array $vehicleIds
     * @param bool $isForVehicleList
     * @return array|null
     */
    private static function handleDriverVehicleParamsAll(
        array $params,
        array $vehicleIds,
        bool $isForVehicleList
    ): ?array {
        $inputVehicleIds = [];
        $hasInputVehicleIds = false;

        if (isset($params['id']) && $isForVehicleList) {
            $hasInputVehicleIds = true;
            $inputVehicleIds = is_array($params['id']) ? $params['id'] : [$params['id']];
        }
        if (isset($params['vehicleIds'])) {
            $hasInputVehicleIds = true;
            $inputVehicleIds = !empty($inputVehicleIds)
                ? array_merge($inputVehicleIds, $params['vehicleIds'])
                : $params['vehicleIds'];
        }
        if (isset($params['driver_id'])) {
            unset($params['driver_id']);
        }

        $resultVehicleIds = array_intersect($vehicleIds, $inputVehicleIds);
        $params['vehicleIds'] = $hasInputVehicleIds
            ? (empty($resultVehicleIds) ? $inputVehicleIds : $resultVehicleIds)
            : $vehicleIds;

        if ($isForVehicleList) {
            $params['id'] = $params['vehicleIds'];
        }

        return $params;
    }

    /**
     * @param array $params
     * @param User $user
     * @param EntityManager $em
     * @return array
     */
    public static function handleUserGroupParams(array $params, User $user, EntityManager $em)
    {
        if ($user->needToCheckUserGroup()) {
            $vehicleIds = $em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
            if (isset($params['id'])) {
                if (is_array($params['id'])) {
                    $params['id'] = array_intersect($vehicleIds, $params['id']);
                } elseif (!in_array($params['id'], $vehicleIds)) {
                    $params['id'] = null;
                }
            } else {
                $params['id'] = $vehicleIds;
            }
        }

        return $params;
    }

    public static function validateVehicleTypeParams($data, $files, $translator)
    {
        $errors = [];
        if (!isset($data['name']) || !$data['name']) {
            $errors['name'] = ['required' => $translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public static function prepareVehicleTypePictures(array $files, FileService $fileService, ?User $currentUser)
    {
        $data = [];

        if (isset($files[VehicleType::DEFAULT_PICTURE])) {
            $data[VehicleType::DEFAULT_PICTURE] = $fileService
                ->uploadVehicleTypePictureFile($files[VehicleType::DEFAULT_PICTURE], $currentUser);
        }
        if (isset($files[VehicleType::DRIVING_PICTURE])) {
            $data[VehicleType::DRIVING_PICTURE] = $fileService
                ->uploadVehicleTypePictureFile($files[VehicleType::DRIVING_PICTURE], $currentUser);
        }
        if (isset($files[VehicleType::IDLING_PICTURE])) {
            $data[VehicleType::IDLING_PICTURE] = $fileService
                ->uploadVehicleTypePictureFile($files[VehicleType::IDLING_PICTURE], $currentUser);
        }
        if (isset($files[VehicleType::STOPPED_PICTURE])) {
            $data[VehicleType::STOPPED_PICTURE] = $fileService
                ->uploadVehicleTypePictureFile($files[VehicleType::STOPPED_PICTURE], $currentUser);
        }

        return $data;
    }

    public static function handleElasticArrayParams(array $params)
    {
        if (isset($params['defaultLabel']) && is_array($params['defaultLabel'])) {
            $params['caseOr']['defaultLabel'] = $params['defaultLabel'];
            unset($params['defaultLabel']);
        }

        return $params;
    }

    /**
     * @param array $params
     * @param EntityManagerInterface $em
     * @param User $user
     * @param bool $isForVehicleList
     * @param bool $isUsedSettingToVehiclesAccess
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public static function handleDriverVehicleParams(
        array $params,
        EntityManagerInterface $em,
        User $user,
        bool $isForVehicleList = true,
        bool $isUsedSettingToVehiclesAccess = false
    ): array {
        if (isset($params['driverIdForHistoryVehicles'])) {
            $driverId = $params['driverIdForHistoryVehicles'];

            // @todo keep validation if $driverId in the same team as $user?
            if ($driverId) {
                $driver = $em->getRepository(User::class)->find($driverId);

                if (!$driver || $driver->getTeamId() != $user->getTeamId()) {
                    return $params;
                }
            }

            $vehicleIds = self::getDriverVehicleIdsBySetting($em, $user, $driverId, $isUsedSettingToVehiclesAccess);

            if (is_null($vehicleIds)) {
                return $params;
            }

            $params = self::handleDriverVehicleParamsAll($params, $vehicleIds, $isForVehicleList);
        }

        return $params;
    }
}