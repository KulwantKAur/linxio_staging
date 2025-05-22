<?php

namespace App\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventTemplate
 */
#[ORM\Table(name: 'notification_event_template')]
#[ORM\UniqueConstraint(columns: ['set_id', 'event_id', 'template_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\EventTemplateRepository')]
class EventTemplate
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(name: 'template_id', type: 'integer')]
    private $templateId;

    /**
     * @var TemplateSet
     */
    #[ORM\ManyToOne(targetEntity: 'TemplateSet', inversedBy: 'eventTemplates')]
    #[ORM\JoinColumn(name: 'set_id', referencedColumnName: 'id', nullable: false)]
    private $set;

    /**
     * @var Event
     */
    #[ORM\ManyToOne(targetEntity: 'Event')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    private $event;

    /**
     * @var Template
     */
    #[ORM\ManyToOne(targetEntity: 'Template')]
    #[ORM\JoinColumn(name: 'template_id', referencedColumnName: 'id', nullable: false)]
    private $template;


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
     * @return TemplateSet
     */
    public function getSet(): TemplateSet
    {
        return $this->set;
    }

    /**
     * @param TemplateSet $set
     * @return $this
     */
    public function setSet(TemplateSet $set)
    {
        $this->set = $set;

        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     * @param Template $template
     * @return $this
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;

        return $this;
    }
}
