<?php

namespace App\Service\SSO\Provider;

use App\Entity\SSOIntegrationData;
use Doctrine\ORM\EntityManagerInterface;

class SSOOktaProvider extends SSOProvider
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ?SSOIntegrationData    $SSOIntegrationData,
    ) {
        parent::__construct($em, $SSOIntegrationData);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->attributes['email'][0];
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->getAttributes()['username'] ?? ($this->getAttributes()['login'] ?? null);
    }

    /**
     * @return string|null
     */
    public function getClientName(): ?string
    {
        return $this->getAttributes()['client'][0] ?? null;
    }

    public function getAbn(): ?string
    {
        return $this->getAttributes()['abn'][0] ?? null;
    }

    public function getChevronAccountId(): ?string
    {
        return $this->getAttributes()['chevronAccountId'][0] ?? null;
    }

    /**
     * @return string|null
     */
    public function getTeamKey(): ?string
    {
        return $this->getChevronAccountId() ?: $this->getAbn();
    }

    /**
     * @return string|null
     */
    public function getRoleName(): ?string
    {
        return $this->getAttributes()['role'][0] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->getAttributes()['phone'][0] ?? null;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttributes()['firstName'][0] ?? $this->getEmail();
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->getAttributes()['lastName'][0] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPosition(): ?string
    {
        return $this->getAttributes()['title'][0] ?? null;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        $status = $this->getAttributes()['status'][0] ?? null;

        return $this->isValidStatus($status) ? $status : null;
    }

    /**
     * @return array
     */
    public function mapUserAttributes(): array
    {
        // @todo map attributes by options or SSOProvider->initAttributes()?
        return [
            'email' => $this->getUsername(),
            'name' => $this->getName(),
            'surname' => $this->getSurname(),
            'phone' => $this->getPhone(),
            'position' => $this->getPosition(),
            'status' => $this->getStatus(),
        ];
    }

    /**
     * @throws \Exception
     */
    public function validateAttributes()
    {
        return parent::validateAttributes();
    }
}
