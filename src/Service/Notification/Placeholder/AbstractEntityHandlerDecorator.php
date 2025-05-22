<?php

namespace App\Service\Notification\Placeholder;

use App\Service\Notification\Placeholder\Interfaces\EventEntityHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractEntityHandlerDecorator implements EventEntityHandlerInterface
{
    public TranslatorInterface $translator;
    protected $entity;
    protected $appFrontUrl;
    protected $context;

    public const DEFAULT_UNKNOWN = '--';
    public const DEFAULT_COMPANY = 'Linxio';
    public const GOOGLE_MAPS_LINK = 'https://maps.google.com';

    public function __construct(
        TranslatorInterface $translator,
        object $entity,
        string $appFrontUrl,
        array $context = []
    ) {
        $this->translator = $translator;
        $this->entity = $entity;
        $this->appFrontUrl = $appFrontUrl;
        $this->context = $context;
    }

    /**
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return string|null
     */
    public function getAppFrontUrl(): ?string
    {
        return ($this->getTeam() && $this->getTeam()->isResellerTeam())
            ? $this->getTeam()->getHostApp()
            : $this->appFrontUrl;
    }

    /**
     * @return string|null
     */
    protected function getFromCompany(): ?string
    {
        if ($this->getTeam()?->isResellerTeam()) {
            return $this->getTeam()->getResellerCompany();
        } elseif ($this->getTeam()?->getClient()?->getReseller()) {
            return $this->getTeam()?->getClient()?->getReseller()?->getCompanyName();
        }

        return self::DEFAULT_COMPANY;
    }

    /**
     * @return string|null
     */
    protected function getTeamName(): ?string
    {
        if ($this->getTeam() && $this->getTeam()->isClientTeam()) {
            return $this->getTeam()->getClientName();
        } elseif ($this->getTeam() && $this->getTeam()->isResellerTeam()) {
            return $this->getTeam()->getResellerCompany();
        }

        return $this->getTeam()->getType() ?? self::DEFAULT_UNKNOWN;
    }
}
