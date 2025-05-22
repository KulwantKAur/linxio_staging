<?php

namespace App\Command;

use App\Entity\Device;
use App\Entity\Notification\Event;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;

#[AsCommand(name: 'app:device:contract-expired')]
class DeviceContractExpiredCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Trigger ntf for devices with expired contract');
    }

    public function __construct(
        private readonly EntityManager $em,
        private readonly NotificationEventDispatcher $notificationDispatcher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $devices = $this->em->getRepository(Device::class)->getDevicesWithContractExpired();

        if (!$devices) {
            return 0;
        }

        $ids = array_map(fn(Device $device) => $device->getId(), $devices);
        $imei = array_map(fn(Device $device) => $device->getImei(), $devices);

        $this->notificationDispatcher->dispatch(
            Event::DEVICE_CONTRACT_EXPIRED,
            reset($devices),
            null,
            ['id' => $ids, 'imei_array' => implode(', ', $imei), 'count' => count($devices)]
        );


        return 0;
    }
}