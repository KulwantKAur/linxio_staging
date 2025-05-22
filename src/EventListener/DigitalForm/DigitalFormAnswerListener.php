<?php

namespace App\EventListener\DigitalForm;

use App\Entity\DigitalFormAnswerStep;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Events\DigitalForm\DigitalFormAnswerEvent;
use App\Service\DigitalForm\DigitalFormStepFactory;
use App\Service\Vehicle\VehicleOdometerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DigitalFormAnswerListener implements EventSubscriberInterface
{
    /** @var VehicleOdometerService */
    private $vehicleOdometerService;


    public function __construct(
        VehicleOdometerService $vehicleOdometerService
    ) {
        $this->vehicleOdometerService = $vehicleOdometerService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DigitalFormAnswerEvent::ANSWER_CREATED => 'onAnswerCreated',
        ];
    }

    public function onAnswerCreated(DigitalFormAnswerEvent $event): void
    {
        $answer = $event->getAnswer();
        $steps = $answer->getDigitalFormAnswerSteps();
        foreach ($steps as $step) {
            $options = $step->getDigitalFormStep()->getOptions();
            if ($options['type'] === DigitalFormStepFactory::TYPE_ODOMETER) {
                $this->handleOdometerStep($step, $answer->getVehicle(), $answer->getUser());
            }
        }
    }

    private function handleOdometerStep(DigitalFormAnswerStep $step, Vehicle $vehicle, User $user): void
    {
        // DB value in meters
        $data = [
            'odometer' => $step->getValue() * 1000,
        ];

        $this->vehicleOdometerService->saveByVehicleAndDataAndUser($vehicle, $data, $user);
    }
}
