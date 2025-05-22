<?php

namespace App\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationTransports
 */
#[ORM\Table(name: 'notification_transports')]
#[ORM\UniqueConstraint(columns: ['notification_id', 'transport_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\NotificationTransportsRepository')]
class NotificationTransports
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Notification
     */
    #[ORM\ManyToOne(targetEntity: 'Notification', inversedBy: 'transports')]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', nullable: false)]
    private $notification;

    /**
     * @var Transport
     */
    #[ORM\ManyToOne(targetEntity: 'Transport')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', nullable: false)]
    private $transport;

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
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     * @return $this
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param Transport $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }
}
