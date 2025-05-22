<?php

namespace App\Service\Notification;

use App\Entity\Notification\NotificationMobileDevice;
use App\Entity\User;
use App\Service\BaseService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationMobileDeviceService extends BaseService
{
    protected TranslatorInterface $translator;
    private EntityManager $em;
    private LoggerInterface $logger;

    /**
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->logger = $logger;
    }


    /**
     * @param array $data
     * @param User $currentUser
     * @return NotificationMobileDevice|void
     */
    public function setMobileDevice(array $data, User $currentUser)
    {
        try {
            /** @var NotificationMobileDevice $mobileDevice */
            $mobileDevice = $this->em->getRepository(NotificationMobileDevice::class)
                ->findOneBy(['deviceToken' => $data['deviceToken']]);

            if (!$mobileDevice) {
                $mobileDevice = new NotificationMobileDevice($data);
                $this->em->persist($mobileDevice);
            } elseif (array_key_exists('deviceToken', $data)) {
                $mobileDevice->setDeviceToken($data['deviceToken']);
            }

            $mobileDevice->setUser($currentUser);
            $mobileDevice->setLastLoggedAt(new \DateTime());

            $this->em->flush();

            return $mobileDevice;
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
        }
    }
}
