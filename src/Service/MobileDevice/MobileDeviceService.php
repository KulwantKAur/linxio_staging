<?php

namespace App\Service\MobileDevice;


use App\Entity\MobileDevice;
use App\Entity\User;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class MobileDeviceService extends BaseService
{
    protected $translator;
    private $em;

    /**
     * DeviceService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em
    ) {
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return MobileDevice|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setMobileDevice(array $data, User $currentUser)
    {
        /** @var MobileDevice $mobileDevice */
        $mobileDevice = $this->em->getRepository(MobileDevice::class)
            ->findOneBy(['deviceId' => $data['deviceId']]);

        if (!$mobileDevice) {
            $mobileDevice = new MobileDevice($data);
            $this->em->persist($mobileDevice);
        } elseif (array_key_exists('loginWithId', $data)) {
            $mobileDevice->setLoginWithId($data['loginWithId']);
            $mobileDevice->setUpdatedAt(new \DateTime());
        }

        $mobileDevice->setTeam($currentUser->getTeam());

        $this->em->flush();

        return $mobileDevice;
    }
}
