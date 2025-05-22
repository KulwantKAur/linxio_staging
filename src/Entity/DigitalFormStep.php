<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DigitalFormStep
 */
#[ORM\Table(name: 'digital_form_step')]
#[ORM\Entity(repositoryClass: 'App\Repository\DigitalFormStepRepository')]
class DigitalFormStep extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalForm', inversedBy: 'digitalFormSteps')]
    #[ORM\JoinColumn(name: 'digital_form_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $digitalForm;

    /**
     * @var int
     */
    #[ORM\Column(name: 'step_order', type: 'smallint')]
    private $stepOrder;

    /**
     * @var string
     */
    #[ORM\Column(name: 'title', type: 'string', length: 1024)]
    private $title;

    /**
     * @var string
     */
    #[ORM\Column(name: 'description', type: 'string', length: 8192)]
    private $description;

    /**
     * @var json
     */
    #[ORM\Column(name: 'condition', type: 'json', nullable: true, options: ['jsonb' => true])]
    private $condition;

    /**
     * @var json
     */
    #[ORM\Column(name: 'options', type: 'json', nullable: false, options: ['jsonb' => true])]
    private $options;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param DigitalForm $digitalForm
     *
     * @return DigitalFormStep
     */
    public function setDigitalForm(DigitalForm $digitalForm)
    {
        $this->digitalForm = $digitalForm;

        return $this;
    }

    /**
     * @return DigitalForm
     */
    public function getDigitalForm()
    {
        return $this->digitalForm;
    }

    /**
     * @param int $stepOrder
     *
     * @return DigitalFormStep
     */
    public function setStepOrder($stepOrder)
    {
        $this->stepOrder = $stepOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getStepOrder()
    {
        return $this->stepOrder;
    }

    /**
     * @param string $title
     *
     * @return DigitalFormStep
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     *
     * @return DigitalFormStep
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $condition
     *
     * @return DigitalFormStep
     */
    public function setCondition($condition = null)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return json
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param mixed $options
     *
     * @return DigitalFormStep
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getParsedOptions(): array
    {
        return array_map(function ($option) {
            return $option === '' ? null : $option;
        }, $this->getOptions());
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $include = []): array
    {
        return [
            'id' => $this->getId(),
            'order' => $this->getStepOrder(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'condition' => $this->getCondition(),
            'options' => $this->getParsedOptions(),
        ];
    }
}
