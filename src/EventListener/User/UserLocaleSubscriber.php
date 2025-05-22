<?php

namespace App\EventListener\User;

use App\Entity\Setting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserLocaleSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;
    private TranslatorInterface $translator;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $setting = $user->getSettingByName(Setting::LANGUAGE_SETTING);
        $locale = $setting ? $setting->getValue() : Setting::LANGUAGE_SETTING_DEFAULT_VALUE;
        $this->translator->setLocale($locale);
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onInteractiveLogin']
        ];
    }
}