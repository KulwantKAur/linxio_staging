<?php

namespace App\Service\TrackerProvider;

use App\Enums\SocketEventEnum;
use App\Util\DateHelper;

class TrackerProviderEvent
{
    /**
     * @param string $name
     * @param SocketEventEnum $source
     * @param \DateTimeInterface $eventTime
     * @param array $data
     */
    public function __construct(
        private string             $name,
        private SocketEventEnum    $source = SocketEventEnum::SourceApi,
        private \DateTimeInterface $eventTime = new \DateTime(),
        private array              $data = [],
    ) {
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'eventTime' => DateHelper::formatDate($this->getEventTime()),
            'source' => $this->getSource()->value,
            'data' => $this->getData(),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return SocketEventEnum
     */
    public function getSource(): SocketEventEnum
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEventTime(): \DateTimeInterface
    {
        return $this->eventTime;
    }

    /**
     * @param \DateTimeInterface $eventTime
     */
    public function setEventTime(\DateTimeInterface $eventTime): void
    {
        $this->eventTime = $eventTime;
    }
}
