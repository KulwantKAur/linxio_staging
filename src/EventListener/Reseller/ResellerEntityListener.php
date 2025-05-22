<?php


namespace App\EventListener\Reseller;

use App\Entity\Reseller;
use App\Entity\Setting;
use App\Service\Setting\TimeZoneService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResellerEntityListener
{
    private $translator;
    private $timeZoneService;

    /**
     * EntityListener constructor.
     * @param TranslatorInterface $translator
     * @param TimeZoneService $timeZoneService
     */
    public function __construct(
        TranslatorInterface $translator,
        TimeZoneService $timeZoneService
    ) {
        $this->translator = $translator;
        $this->timeZoneService = $timeZoneService;
    }

    public function postLoad(Reseller $reseller, LifecycleEventArgs $args)
    {
        $timezoneSetting = $reseller->getTimeZoneSetting();

        if ($timezoneSetting) {
            $timezoneEntity = $this->timeZoneService->getTimeZoneById($timezoneSetting->getValue());
        } else {
            $timezoneEntity = $this->timeZoneService->getDefaultTimeZone();
        }

        if ($timezoneEntity) {
            $reseller->setTimeZone($timezoneEntity);
        }

        $languageSetting = $reseller->getLanguageSetting() ? $reseller->getLanguageSetting()->getValue() : Setting::LANGUAGE_SETTING_DEFAULT_VALUE;

        if ($timezoneEntity) {
            $reseller->setLanguage($languageSetting);
        }

        return $reseller;
    }
}