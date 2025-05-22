<?php

namespace App\Service\Setting;

use App\Entity\TimeZone;
use App\Exceptions\ValidationException;
use App\Repository\TimeZoneRepository;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class TimeZoneService extends BaseService
{
    private $em;
    private $timezones;
    private $defaultTimeZone;
    private $translator;
    private $settingService;

    /**
     * SettingService constructor.
     * @param EntityManager $em
     * @param TranslatorInterface $translator
     * @param SettingService $settingService
     */
    public function __construct(EntityManager $em, TranslatorInterface $translator, SettingService $settingService)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->settingService = $settingService;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getTimeZoneById($id)
    {
        if (!$this->timezones) {
            $this->loadTimeZones();
        }
        return $this->timezones[$id] ?? null;
    }

    /**
     * @return mixed
     */
    public function getDefaultTimeZone()
    {
        if (!$this->defaultTimeZone) {
            $this->loadTimeZones();
        }

        return $this->defaultTimeZone;
    }

    public function loadTimeZones()
    {
        $timezones = $this->em->getRepository(TimeZone::class)->findAll();
        foreach ($timezones as $tz) {
            $this->timezones[$tz->getId()] = $tz;
            if ($tz->getName() === TimeZone::DEFAULT_TIMEZONE['name']) {
                $this->defaultTimeZone = $tz;
            }
        }
    }

    /**
     * @param array $data
     * @return object|null
     * @throws ValidationException
     */
    public function getTimezone(array $data): ?TimeZone
    {
        /** @var TimeZoneRepository $tzRepo */
        $tzRepo = $this->em->getRepository(TimeZone::class);

        if (isset($data['timezone']) && $data['timezone']) {
            $tz = $tzRepo->find($data['timezone']);

            if (null === $tz) {
                throw (new ValidationException())->setErrors(
                    [
                        'timezone' => ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')]
                    ]
                );
            }
        } else {
            $tz = $tzRepo->findOneBy(['name' => TimeZone::DEFAULT_TIMEZONE['name']]);
        }

        return $tz;
    }

    public function setTimeZone($timeZoneId, $team = null, $role = null, $user = null): ?TimeZone
    {
        $setting = $this->settingService->setTimezoneSetting($timeZoneId, $team, $role, $user);

        return $this->em->getRepository(TimeZone::class)->find($setting->getValue());
    }
}