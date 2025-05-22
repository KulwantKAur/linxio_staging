<?php

namespace App\EventListener\DigitalForm;

use App\Entity\DigitalFormStep;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\DigitalForm\DigitalFormEvent;
use App\Service\DigitalForm\DigitalFormStepFactory;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Vehicle\VehicleOdometerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DigitalFormListener implements EventSubscriberInterface
{
    /** @var VehicleOdometerService */
    private $vehicleOdometerService;

    /** @var EntityHistoryService */
    private $entityHistoryService;


    public function __construct(
        VehicleOdometerService $vehicleOdometerService,
        EntityHistoryService $entityHistoryService
    ) {
        $this->vehicleOdometerService = $vehicleOdometerService;
        $this->entityHistoryService = $entityHistoryService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DigitalFormEvent::FORM_CREATE => 'onFormCreate',
            DigitalFormEvent::FORM_DELETE => 'onFormDelete',
            DigitalFormEvent::FORM_EDIT => 'onFormEdit',
            DigitalFormEvent::FORM_GET => 'onFormGet',
            DigitalFormEvent::FORM_RESTORE => 'onFormRestore',
        ];
    }

    public function onFormGet(DigitalFormEvent $event): void
    {
        $form = $event->getForm();
        $steps = $form->getDigitalFormSteps();
        foreach ($steps as $step) {
            $options = $step->getOptions();
            if ($options['type'] === DigitalFormStepFactory::TYPE_ODOMETER) {
                $this->handleOdometerStep($step, $event->getVehicle());
            }
        }
    }

    public function onFormCreate(DigitalFormEvent $event): void
    {
        $form = $event->getForm();
        $this->entityHistoryService->create($form, time(), EntityHistoryTypes::DIGITAL_FORM_CREATED, $form->getCreatedBy());
    }

    public function onFormDelete(DigitalFormEvent $event): void
    {
        $form = $event->getForm();
        $this->entityHistoryService->create($form, time(), EntityHistoryTypes::DIGITAL_FORM_DELETED);
    }

    public function onFormEdit(DigitalFormEvent $event): void
    {
        $form = $event->getForm();
        $this->entityHistoryService->create($form, time(), EntityHistoryTypes::DIGITAL_FORM_EDITED, $form->getCreatedBy());
    }

    public function onFormRestore(DigitalFormEvent $event): void
    {
        $form = $event->getForm();
        $this->entityHistoryService->create($form, time(), EntityHistoryTypes::DIGITAL_FORM_RESTORED);
    }

    private function handleOdometerStep(DigitalFormStep $step, Vehicle $vehicle): void
    {
        // get original step options
        $options = $step->getOptions();
        $odometer = $this->vehicleOdometerService->getOdometerValueByVehicleAndOccurredAt($vehicle);

        if (!$odometer || $odometer < 0) {
            $options['default'] = 0;
            unset($options['range']);
        } else {
            // DB value in meters
            $options['default'] = intval($odometer / 1000);
        }

        // update step options
        $step->setOptions($options);
    }
}
