<?php


namespace App\EventListener\Client;

use App\Entity\Client;
use App\Entity\Setting;
use App\Exceptions\ValidationException;
use App\Service\Client\StatusTransitionService;
use App\Service\Setting\TimeZoneService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientEntityListener
{
    private $transitionService;
    private $translator;
    private $timeZoneService;

    private function updateClientDevices(Client $client)
    {
        $devices = $client->getTeam()->getDevices()?->toArray();

        if ($devices) {
            $this->deviceObjectPersister->replaceMany($devices);
        }
    }

    /**
     * ClientEntityListener constructor.
     * @param StatusTransitionService $transitionService
     * @param TranslatorInterface $translator
     * @param TimeZoneService $timeZoneService
     * @param EntityManager $entityManager
     * @param ObjectPersister $deviceObjectPersister
     */
    public function __construct(
        StatusTransitionService $transitionService,
        TranslatorInterface $translator,
        TimeZoneService $timeZoneService,
        private readonly ObjectPersister $deviceObjectPersister,
        private readonly EntityManager $entityManager
    ) {
        $this->transitionService = $transitionService;
        $this->translator = $translator;
        $this->timeZoneService = $timeZoneService;
    }

    public function postLoad(Client $client, PostLoadEventArgs $args)
    {
        $timezoneSetting = $client->getTimeZoneSetting();

        if ($timezoneSetting) {
            $timezoneEntity = $this->timeZoneService->getTimeZoneById($timezoneSetting->getValue());
        } else {
            $timezoneEntity = $this->timeZoneService->getDefaultTimeZone();
        }

        if ($timezoneEntity) {
            $client->setTimeZone($timezoneEntity);
        }

        $languageSetting = $client->getLanguageSetting() ? $client->getLanguageSetting()->getValue() : Setting::LANGUAGE_SETTING_DEFAULT_VALUE;
        $client->setLanguage($languageSetting);
        $client->setEntityManager($this->entityManager);

        return $client;
    }

    /**
     * @param Client $client
     * @param PreUpdateEventArgs $event
     * @throws ValidationException
     */
    public function preUpdate(Client $client, PreUpdateEventArgs $event)
    {
        $this->checkStatusTransition($event);

        if ($event->hasChangedField('chevronAccountId')) {
            $this->updateClientDevices($client);
        }
    }

    /**
     * @param PreUpdateEventArgs $event
     * @throws ValidationException
     */
    private function checkStatusTransition(PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('status')) {
            $isAvailable = $this->transitionService->isAvailable(
                $event->getOldValue('status'),
                $event->getNewValue('status')
            );

            if (!$isAvailable) {
                throw (new ValidationException())->setErrors(
                    ['status' => ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')]]
                );
            }
        }
    }

    public function postPersist(Client $client, PostPersistEventArgs $args)
    {
        $client->setEntityManager($this->entityManager);
    }
}