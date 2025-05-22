<?php

namespace App\EventListener\User;

use App\Entity\Setting;
use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use App\Service\Setting\SettingService;
use App\Service\Setting\TimeZoneService;
use App\Service\User\UserService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserEntityListener
{
    private $tokenStorage;
    private $container;
    private $userService;
    private $timeZoneService;
    private $settingService;
    private EntityManager $entityManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContainerInterface    $container,
        UserService           $userService,
        TimeZoneService       $timeZoneService,
        SettingService        $settingService,
        EntityManager         $entityManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->userService = $userService;
        $this->timeZoneService = $timeZoneService;
        $this->settingService = $settingService;
        $this->entityManager = $entityManager;
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event)
    {
        $this->preUpdateProcess($user, $event);
        $this->checkUpdatePhone($user, $event);
    }

    public function postLoad(User $user, PostLoadEventArgs $args)
    {
        $otpSetting = $user->getSettingByName(Setting::OTP_SETTING);
        if ($otpSetting) {
            $user->setIs2FAEnabled($otpSetting->getValue());
        }

        $timezoneSetting = $user->getSettingByName(Setting::TIMEZONE_SETTING);

        if ($timezoneSetting) {
            $timezoneEntity = $this->timeZoneService->getTimeZoneById($timezoneSetting->getValue());
        } else {
            $timezoneEntity = $this->timeZoneService->getDefaultTimeZone();
        }
        $user->setTimezone($timezoneEntity);

        $languageSetting = $user->getLanguageSetting() ? $user->getLanguageSetting()->getValue() : Setting::LANGUAGE_SETTING_DEFAULT_VALUE;

        if ($languageSetting) {
            $user->setLanguage($languageSetting);
        }

//        if ($user->isDriverClientOrDualAccount()) {
//            $user->setLastRoute($args->getObjectManager()->getRepository(Route::class)->getDriverLastRoute($user));
//            $user->setTodayData($this->userService->getDailyData($user));
//        }

        $loginWithIdSetting = $user->getTeam()->getSettingsByName(Setting::LOGIN_WITH_ID);
        $loginWithIdValue = $loginWithIdSetting ? (bool) $loginWithIdSetting->getValue() : false;

        $user->setCanLoginWithId($loginWithIdValue);
        $user->setEntityManager($this->entityManager);
        $user->setUserService($this->userService);

        return $user;
    }

    public function prePersist(User $user, PrePersistEventArgs $args)
    {
        $user->setEntityManager($this->entityManager);

        return $user;
    }

    /**
     * @param User $user
     * @param PreUpdateEventArgs $event
     */
    private function preUpdateProcess(User $user, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('status')) {
            $em = $this->container->get('doctrine.orm.history_entity_manager');
            $entityHistoryService = $this->container->get("app.entity_history_service");
            $entityHistoryService->setEntityManager($em);
            $createdById = $this->tokenStorage->getToken() &&
            $this->tokenStorage->getToken()->getUser() instanceof User
                ? $this->tokenStorage->getToken()->getUser()->getId() : null;
            $entityHistoryService->create(
                $user,
                $user->getStatus(),
                EntityHistoryTypes::USER_STATUS,
                null,
                $createdById
            );
        }

        if (
            !$event->hasChangedField('lastLoggedAt')
            && !$event->hasChangedField('updatedAt')
            && !$event->hasChangedField('updatedBy')
        ) {
            /** @var User $authUser */
            $authUser = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
            if ($authUser instanceof User) {
                $user->setUpdatedBy($authUser);
                $user->setUpdatedAt(Carbon::now('UTC'));
            }
        }
    }

    private function checkUpdatePhone(User $user, PreUpdateEventArgs $event)
    {
        /** Todo: trigger the `ClientUser PhoneVerified invalidate` event. */
        if ($event->hasChangedField('phone') && $user->isPhoneVerified() && $user->is2FAEnabled()) {
            $user->unverifyPhone();
        }
    }

    public function postPersist(User $user, PostPersistEventArgs $args)
    {
        $user->setEntityManager($this->entityManager);
        $this->settingService->createDefaultUserSettings($user);
    }
}