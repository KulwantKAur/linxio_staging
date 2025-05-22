<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SSOIntegration
 */
#[ORM\Table(name: 'sso_integration')]
#[ORM\Entity(repositoryClass: 'App\Repository\SSOIntegrationRepository')]
class SSOIntegration extends BaseEntity
{
    public const OKTA = 'okta';
    public const MICROSOFT_AZURE = 'ms_azure';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'label', type: 'string', length: 255)]
    private string $label;

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();
        $data['name'] = $this->getName();
        $data['label'] = $this->getLabel();

        return $data;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }
}
