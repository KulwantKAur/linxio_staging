<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduledReportRecipients
 */
#[ORM\Table(name: 'scheduled_report_recipients')]
#[ORM\Entity(repositoryClass: 'App\Repository\ScheduledReportRecipientsRepository')]
class ScheduledReportRecipients extends BaseEntity
{
    public const TYPE_USER = 'users_list';
    public const TYPE_ROLE = 'role';
    public const TYPE_USER_GROUP = 'user_groups_list';
    public const TYPE_EMAIL = 'other_email';

    public function __construct(array $fields = [])
    {
        $this->type = $fields['type'] ?? null;
        $this->value = $fields['value'] ?? null;
        $this->custom = $fields['custom'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;
        $data['type'] = $this->getType();
        $data['value'] = $this->getValue();
        $data['emails'] = $this->getEmails();

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 50, nullable: true)]
    private $type;

    /**
     * @var json_array
     */
    #[ORM\Column(name: 'value', type: 'json', nullable: true)]
    private $value;

    /**
     * @var json_array|null
     */
    #[ORM\Column(name: 'custom', type: 'json', nullable: true)]
    private $custom;

    /**
     * @var ScheduledReport
     */
    #[ORM\OneToOne(targetEntity: 'ScheduledReport', mappedBy: 'recipient', cascade: ['persist'])]
    private $scheduledReport;


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
     * Set type.
     *
     * @param string $type
     *
     * @return ScheduledReportRecipients
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value.
     *
     * @param json $value
     *
     * @return ScheduledReportRecipients
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return json
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set custom.
     *
     * @param json|null $custom
     *
     * @return ScheduledReportRecipients
     */
    public function setCustom($custom = null)
    {
        $this->custom = $custom;

        return $this;
    }

    /**
     * Get custom.
     *
     * @return json|null
     */
    public function getCustom()
    {
        return $this->custom;
    }

    public function getEmails()
    {
        return array_filter($this->getCustom()['emails'] ?? []);
    }
}
