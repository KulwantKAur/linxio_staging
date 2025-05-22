<?php


namespace App\Service\Device\Services;


use App\Entity\Device;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class AdminDeviceService extends DeviceServiceAbstract
{
    private $em;
    protected $currentUser;

    public function __construct(EntityManager $em, User $currentUser)
    {
        $this->em = $em;
        $this->currentUser = $currentUser;
    }

    public function getById(int $id): ?Device
    {
        return $this->em->getRepository(Device::class)->find($id);
    }

    public function prepareDeviceListParams(array $params): array
    {
        if ($this->currentUser->isInstaller()) {
            $params['status'] = Device::STATUS_IN_STOCK;
        }

        return $params;
    }
}