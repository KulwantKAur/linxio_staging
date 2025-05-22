<?php

namespace App\Service\Streamax\Model;

class StreamaxAlarmFile extends StreamaxModel
{
    public ?string $alarmId;
    public ?string $downloadStatus; // COMPLETED, FAILED
    public ?string $error;
    public array $files;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->alarmId = $fields['alarmId'] ?? null;
        $this->downloadStatus = $fields['downloadStatus'] ?? null;
        $this->error = $fields['error'] ?? null;
        $this->files = $fields['files'] ?? [];
    }

    /**
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'alarmId' => $this->getAlarmId(),
            'downloadStatus' => $this->getDownloadStatus(),
            'files' => $this->getFiles(),
        ];
    }

    /**
     * @return string|null
     */
    public function getAlarmId(): ?string
    {
        return $this->alarmId;
    }

    /**
     * @return string|null
     */
    public function getDownloadStatus(): ?string
    {
        return $this->downloadStatus;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}

