<?php

namespace App\Service\SSO\Provider;

use App\Entity\Client;
use App\Entity\Reseller;
use App\Entity\Role;
use App\Entity\SSOIntegration;
use App\Entity\SSOIntegrationData;
use App\Entity\Team;
use App\Entity\User;
use App\Exceptions\SSOException;
use App\Service\SSO\SSOSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class SSOProvider
{
    private function createResellerUser($reseller): User
    {
        if ($reseller->getTeam() !== $this->getSSOIntegrationData()->getTeam()) {
            throw new SSOException(
                'Reseller team does not equal to this integration for reseller',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $roleName = $this->processRoleForReseller($this->getRoleName());
        $roleEntity = $this->em->getRepository(Role::class)->findOneBy([
            'name' => $roleName,
            'team' => Team::TEAM_RESELLER
        ]);

        if (!$roleEntity) {
            throw new SSOException(
                'Role entity is not found for name: ' . $roleName,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user = new User($this->mapUserAttributes());
        $user->setSSOIntegrationData($this->getSSOIntegrationData());
        $user->setRole($roleEntity);
        $user->setTeam($reseller->getTeam());

        return $user;
    }

    private function createResellerClientUser(): User
    {
        $client = $this->getClientByMapping();

        if (!$client) {
            throw new SSOException(
                'Client entity is not found for key: ' . $this->getTeamKey(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if (!$client->getOwnerTeam() || $client->getOwnerTeam() !== $this->getSSOIntegrationData()->getTeam()) {
            throw new SSOException(
                'Client team does not belong to this integration for reseller',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $roleName = $this->processRoleForClientOfReseller($this->getRoleName());
        $roleEntity = $this->em->getRepository(Role::class)->findOneBy([
            'name' => $roleName,
            'team' => Team::TEAM_CLIENT
        ]);

        if (!$roleEntity) {
            throw new SSOException(
                'Role entity is not found for name: ' . $roleName,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user = new User($this->mapUserAttributes());
        $user->setSSOIntegrationData($this->getSSOIntegrationData());
        $user->setRole($roleEntity);
        $user->setTeam($client->getTeam());

        return $user;
    }

    private function createUserForResellerTeamType(): User
    {
        $reseller = $this->getResellerByMapping();

        return $reseller ? $this->createResellerUser($reseller) : $this->createResellerClientUser();
    }

    private function createUserForClientTeamType(): User
    {
        $roleName = $this->processRoleForClient($this->getRoleName());
        $roleEntity = $this->em->getRepository(Role::class)->findOneBy([
            'name' => $roleName,
            'team' => Team::TEAM_CLIENT
        ]);

        if (!$roleEntity) {
            throw new SSOException(
                'Role entity is not found for name: ' . $roleName,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $client = $this->getClientByMapping();

        if (!$client) {
            throw new SSOException(
                'Client entity is not found for key: ' . $this->getTeamKey(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user = new User($this->mapUserAttributes());
        $user->setSSOIntegrationData($this->getSSOIntegrationData());
        $user->setRole($roleEntity);
        $user->setTeam($client->getTeam());

        return $user;
    }

    private function processRoleForClient(?string $roleName): string
    {
        $isAllowed = match ($roleName) {
            Role::ROLE_ADMIN,
            Role::ROLE_MANAGER,
            Role::ROLE_CLIENT_DRIVER => true,
            default => false
        };

        if (!$isAllowed) {
            throw new SSOException(
                'Role is not allowed for client: ' . $roleName,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $roleName;
    }

    /**
     * @param string|null $roleName
     * @return string
     * @throws SSOException
     */
    private function processRoleForReseller(?string $roleName): string
    {
        $isAllowed = match ($roleName) {
            Role::ROLE_ADMIN,
            Role::ROLE_MANAGER,
            Role::ROLE_ACCOUNT_MANAGER,
            Role::ROLE_SUPPORT,
            Role::ROLE_INSTALLER => true,
            default => false
        };

        if (!$isAllowed) {
            throw new SSOException(
                'Role is not allowed for reseller: ' . $roleName,
                Response::HTTP_UNPROCESSABLE_ENTITY)
            ;
        }

        return match ($roleName) {
            Role::ROLE_ADMIN => Role::ROLE_RESELLER_ADMIN,
            Role::ROLE_ACCOUNT_MANAGER,
            Role::ROLE_MANAGER => Role::ROLE_RESELLER_ACCOUNT_MANAGER,
            Role::ROLE_SUPPORT => Role::ROLE_RESELLER_SUPPORT,
            Role::ROLE_INSTALLER => Role::ROLE_RESELLER_INSTALLER,
            default => throw new SSOException('Role is not found ' . $roleName, Response::HTTP_UNPROCESSABLE_ENTITY)
        };
    }

    private function processRoleForClientOfReseller(?string $roleName): string
    {
        return $this->processRoleForClient($roleName);
    }

    private function getClientByMapping(): ?Client
    {
        if ($this->isWithBusinessId()) {
            return match (true) {
                $this->isChevron() => $this->em->getRepository(Client::class)
                    ->getClientByChevronAccountId($this->getTeamKey()),
                default => $this->em->getRepository(Client::class)->getClientByAbn($this->getTeamKey()),
            };
        }

        return $this->em->getRepository(Client::class)->getClientByTeamId($this->getSSOIntegrationData()->getTeamId());
    }

    private function getResellerByMapping(): ?Reseller
    {
        if ($this->isWithBusinessId()) {
            return match (true) {
                $this->isChevron() => $this->em->getRepository(Reseller::class)
                    ->getResellerByChevronAccountId($this->getTeamKey()),
                default => $this->em->getRepository(Reseller::class)->getResellerByAbn($this->getTeamKey()),
            };
        }

        return $this->em->getRepository(Reseller::class)
            ->getResellerByTeamId($this->getSSOIntegrationData()->getTeamId());
    }

    private function isWithBusinessId(): bool
    {
        return $this->getAbn() || $this->getChevronAccountId();
    }

    public function __construct(
        protected EntityManagerInterface $em,
        protected ?SSOIntegrationData    $SSOIntegrationData,
        protected array                  $attributes = [],
    ) {
    }

    /**
     * @return SSOIntegrationData
     */
    public function getSSOIntegrationData(): SSOIntegrationData
    {
        return $this->SSOIntegrationData;
    }

    public function mapUserAttributes(): array
    {
        return $this->getAttributes();
    }

    public function createUser(string $username, array $attributes): User
    {
        $this->initAttributes($attributes);

        return match ($this->getSSOIntegrationData()->getTeamType()) {
            Team::TEAM_CLIENT => $this->createUserForClientTeamType(),
            Team::TEAM_RESELLER => $this->createUserForResellerTeamType(),
            default => throw new SSOException(
                'You have to add team for integration',
                Response::HTTP_UNPROCESSABLE_ENTITY
            ),
        };
    }

    /**
     * @param SSOIntegrationData $SSOIntegrationData
     * @param string $certificate
     * @param array $settings
     * @return array
     */
    public function mapSettings(SSOIntegrationData $SSOIntegrationData, string $certificate, array $settings): array
    {
        // @todo replace default options with integration options
        $integrationSettings = $SSOIntegrationData->getSettings();
        $settings = $integrationSettings ? array_replace_recursive($settings, $integrationSettings) : $settings;
        $SSOSettings = new SSOSettings($settings);
        $SSOSettings->setIdpEntityId($SSOIntegrationData->getIdpEntityId());
        $SSOSettings->setIdpX509cert($certificate);
        $SSOSettings->setIdpSSOURL($SSOIntegrationData->getIdpSSOUrl());
        $SSOSettings->setIdpSLOURL($SSOIntegrationData->getIdpSLOUrl());

        return $SSOSettings->getSettings();
    }

    /**
     * @param SSOIntegrationData $SSOIntegrationData
     * @return $this
     */
    public function getInstance(SSOIntegrationData $SSOIntegrationData): self
    {
        return match ($SSOIntegrationData->getIntegrationName()) {
            SSOIntegration::OKTA => new SSOOktaProvider($this->em, $SSOIntegrationData),
            SSOIntegration::MICROSOFT_AZURE => new SSOAzureProvider($this->em, $SSOIntegrationData),
            default => new self($this->em, $SSOIntegrationData),
        };
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function initAttributes(array $attributes): void
    {
        $this->setAttributes($attributes);
        $this->validateAttributes();
        // @todo map attributes by options or $this->mapUserAttributes?
    }

    public function validateAttributes()
    {
        if (!$this->getRolename()) {
            throw new SSOException('Field \'role\' is required', Response::HTTP_BAD_REQUEST);
        }
    }

    public function getTeamKey(): ?string
    {
        return null;
    }

    public function getRoleName(): ?string
    {
        return null;
    }

    public function isValidStatus(?string $status): bool
    {
        return in_array($status, User::ALLOWED_STATUSES);
    }

    public function isStatusBlocked(): bool
    {
        return $this->getStatus() && $this->getStatus() == User::STATUS_BLOCKED;
    }

    public function isChevron(): bool
    {
        return boolval($this->getSSOIntegrationData()->getTeam()?->isChevron());
    }

    public function getAbn(): ?string
    {
        return null;
    }

    public function getChevronAccountId(): ?string
    {
        return null;
    }
}
