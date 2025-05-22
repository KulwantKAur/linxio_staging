<?php


namespace App\Service\Device\Services;


use App\Entity\Device;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

abstract class DeviceServiceAbstract
{
    abstract public function __construct(EntityManager $em, User $currentUser);

    abstract public function getById(int $id): ?Device;

    public function prepareDeviceListParams(array $params): array
    {
        return $params;
    }

    public function handleDeviceModelAndVendor(array $params): array
    {
        if (!$this->currentUser->isInAdminTeam()) {
            if (isset($params['model'])) {
                $params['modelAlias'] = $params['model'];
                unset($params['model']);
            }
            if (isset($params['vendor'])) {
                $params['vendorAlias'] = $params['vendor'];
                unset($params['vendor']);
            }
        }

        return $params;
    }
}