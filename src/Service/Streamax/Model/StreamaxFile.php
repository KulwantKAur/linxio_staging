<?php

namespace App\Service\Streamax\Model;

class StreamaxFile extends StreamaxModel
{
    public ?string $fileId; // evidence file’s id
    public ?string $fileType; // VIDEO, IMAGE, BLACK_BOX
    public ?int $channel;
    public ?string $startTime; // RFC3339
    public ?string $endTime; // RFC3339
    public ?string $url;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fileId = $fields['fileId'] ?? null;
        $this->fileType = $fields['fileType'] ?? null;
        $this->channel = $fields['channel'] ?? null;
        $this->startTime = $fields['startTime'] ?? null;
        $this->endTime = $fields['endTime'] ?? null;
        $this->url = $fields['url'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    /**
     * @return string|null
     */
    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    /**
     * @return int|null
     */
    public function getChannel(): ?int
    {
        return $this->channel;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStartTimeAsDate(): ?\DateTimeInterface
    {
        return $this->getStartTime() ? $this->getDateTimeFromString($this->getStartTime()) : null;
    }

    /**
     * @return string|null
     */
    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEndTimeAsDate(): ?\DateTimeInterface
    {
        return $this->getEndTime() ? $this->getDateTimeFromString($this->getEndTime()) : null;
    }
}

