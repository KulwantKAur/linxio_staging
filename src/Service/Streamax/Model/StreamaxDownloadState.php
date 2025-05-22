<?php

namespace App\Service\Streamax\Model;

class StreamaxDownloadState extends StreamaxModel
{
    public ?string $alarmId;
    public ?string $state; // COMPLETED, FAILED
    public ?array $fileList;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->alarmId = $fields['alarmId'];
        $this->state = $fields['state'];
        $this->fileList = $fields['fileList'] ?? [];
    }

    /**
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'alarmId' => $this->getAlarmId(),
            'state' => $this->getState(),
            'fileList' => $this->getFileList(),
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
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return array|StreamaxDownloadStateFile[]|null
     */
    public function getFileList(): ?array
    {
        return $this->fileList;
    }
}

