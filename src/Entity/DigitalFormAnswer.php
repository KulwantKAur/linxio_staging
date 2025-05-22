<?php

declare(strict_types=1);

namespace App\Entity;

use App\Util\DateHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * DigitalFormAnswer
 */
#[ORM\Table(name: 'digital_form_answer')]
#[ORM\Entity(repositoryClass: 'App\Repository\DigitalFormAnswerRepository')]
class DigitalFormAnswer extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var DigitalForm
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalForm', inversedBy: 'digitalFormAnswers')]
    #[ORM\JoinColumn(name: 'digital_form_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $digitalForm;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $user;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'DigitalFormAnswerStep', mappedBy: 'digitalFormAnswer', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $digitalFormAnswerSteps;

    public const DEFAULT_DISPLAY_VALUES = [
        'form',
        'vehicle',
        'user',
        'isPass',
        'duration',
        'statusRatio',
    ];

    public const PASS = 'Pass';
    public const FAIL = 'Fail';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->digitalFormAnswerSteps = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDigitalForm(DigitalForm $digitalForm): self
    {
        $this->digitalForm = $digitalForm;

        return $this;
    }

    public function getDigitalForm(): DigitalForm
    {
        return $this->digitalForm;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getDigitalFormAnswerSteps(): Collection
    {
        return $this->digitalFormAnswerSteps;
    }

    public function getStepsSorted(): Collection
    {
        $iterator = $this->digitalFormAnswerSteps->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getDigitalFormStep()->getStepOrder() < $b->getDigitalFormStep()->getStepOrder()) ? -1 : 1;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function addDigitalFormAnswerStep(DigitalFormAnswerStep $digitalFormAnswerStep): self
    {
        $this->digitalFormAnswerSteps->add($digitalFormAnswerStep);

        return $this;
    }

    public function getDuration(): int
    {
        $duration = 0;
        foreach ($this->getDigitalFormAnswerSteps() as $answer) {
            $duration += $answer->getDuration();
        }
        return $duration;
    }

    public function getIsPass()
    {
        foreach ($this->getDigitalFormAnswerSteps() as $answer) {
            if ($answer->getIsPass() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->getId(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
        ];

        if (in_array('form', $include, true)) {
            $data['form'] = $this->getDigitalForm()->toArray(['creator']);
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle() ? $this->getVehicle()->toArray(Vehicle::REPORT_VALUES) : null;
        }
        if (in_array('user', $include, true)) {
            $data['user'] = $this->getUser()->toArray(User::SIMPLE_VALUES);
        }
        if (in_array('isPass', $include, true)) {
            $data['isPass'] = $this->getIsPass();
        }
        if (in_array('duration', $include, true)) {
            $data['duration'] = $this->getDuration();
        }
        if (in_array('answers', $include, true)) {
            foreach ($this->getDigitalFormAnswerSteps() as $item) {
                $data['answers'][] = $item->toArray($include);
            }
        }
        if (in_array('statusRatio', $include, true)) {
            $data['statusRatio'] = $this->getStatusRatio();
        }

        return $data;
    }

    public function toExport(array $include = [], ?User $user = null): array
    {
        $data = [];

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }
        if (in_array('frequency', $include, true)) {
            $data['frequency'] = $this->getDigitalForm()->getInspectionPeriod()
                ? DigitalForm::INSPECTION_PERIOD_TEXT_VALUES[$this->getDigitalForm()->getInspectionPeriod()]
                : null;
        }
        if (in_array('isPass', $include, true)) {
            $data['isPass'] = $this->getIsPass() ? self::PASS : self::FAIL;
        }
        if (in_array('regNo', $include, true)) {
            $data['regNo'] = $this->getVehicle() ? $this->getVehicle()->getRegNo() : '';
        }
        if (in_array('date', $include, true)) {
            $data['date'] = $this->formatDate(
                $this->getCreatedAt(),
                self::EXPORT_DATE_FORMAT,
                $user?->getTimezone()
            );
        }
        if (in_array('user', $include, true)) {
            $data['user'] = $this->getUser()->getFullName();
        }
        if (in_array('performedBy', $include, true)) {
            $data['performedBy'] = $this->getUser()->getFullName();
        }
        if (in_array('duration', $include, true)) {
            $data['duration'] = $this->getDuration() ? DateHelper::seconds2human($this->getDuration()) : null;
        }
        if (in_array('formTitle', $include, true)) {
            $data['formTitle'] = $this->getDigitalForm()->getTitle();
        }
        if (in_array('inspectionFormTitle', $include, true)) {
            $data['inspectionFormTitle'] = $this->getDigitalForm()->getTitle();
        }

        return $data;
    }

    public function getStatusRatio(): string
    {
        $fail = $pass = 0;
        foreach ($this->getDigitalFormAnswerSteps() as $item) {
            if ($item->getIsPass() === true) {
                $pass++;
            } elseif ($item->getIsPass() === false) {
                $fail++;
            }
        }

        return $pass . '/' . ($pass + $fail);
    }

    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone($this->getUser()->getTimezone());
    }

    public function getDate(): \DateTime
    {
        return $this->getCreatedAt()->setTimezone($this->getTimezone());
    }
}
