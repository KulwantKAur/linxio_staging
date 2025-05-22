<?php

namespace App\Command\Tracker;

use App\Entity\DeviceSensor;
use App\Entity\Notification\Event;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Command\Traits\BreakableTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:update-device-sensor-status')]
class UpdateDeviceSensorStatusCommand extends Command
{
    use LockableTrait, BreakableTrait;

    private $em;
    private $notificationDispatcher;
    private $params;

    protected function configure(): void
    {
        $this->setDescription('Update sensor status');
    }

    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->params = $params;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        /** @var Event $eventStatus */
        $eventStatus = $this->em->getRepository(Event::class)->findOneBy(['name' => Event::SENSOR_STATUS]);

        $deviceSensors = $this->em->getRepository(DeviceSensor::class)->getOnlineDeviceSensors();

        $progressBar = new ProgressBar($output, count($deviceSensors));
        $progressBar->start();

        /** @var DeviceSensor $deviceSensor */
        foreach ($deviceSensors as $deviceSensor) {
            try {
                if ($deviceSensor->getSensor()->getStatus() === DeviceSensor::STATUS_ONLINE
                    && !$deviceSensor->getSensor()->isStatusOnline()) {
                    $deviceSensor->setStatus(DeviceSensor::STATUS_OFFLINE);
                    $deviceSensor->getSensor()->getLastDeviceSensor()->setStatus(DeviceSensor::STATUS_OFFLINE);
                    $this->em->flush();

                    $this->notificationDispatcher->dispatch(
                        $eventStatus->getName(),
                        $deviceSensor->getSensor()->getLastDeviceSensor()->getLastTrackerHistorySensor(),
                        new \DateTime()
                    );
                }
            } catch (\Exception $exception) {
                $output->writeln(
                    PHP_EOL . sprintf(
                        'Error with deviceSensorId: %s with message: %s',
                        $deviceSensor->getId(),
                        $exception->getMessage()
                    )
                );
            }

            $progressBar->advance();
        }

        $this->em->clear();

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Sensor status successfully updated!');

        return 0;
    }
}