<?php


namespace App\Service\Device\Services;


use App\Entity\Device;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class ClientDeviceService extends DeviceServiceAbstract
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
        return $this->em->getRepository(Device::class)->findOneBy(
            [
                'id' => $id,
                'team' => $this->currentUser->getTeam()
            ]
        );
    }

    public function prepareDeviceListParams(array $params): array
    {
        $params['clientId'] = $this->currentUser->getTeam()->getClientId();
        $params = $this->handleDeviceModelAndVendor($params);

        return $params;
    }
}