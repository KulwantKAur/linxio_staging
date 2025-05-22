<?php

namespace App\Service\Streamax\Model;

class StreamaxEvidence extends StreamaxModel
{
    public ?string $alarmId;
    public ?string $evidenceId;
    public array $fileList;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->alarmId = $fields['alarmId'];
        $this->evidenceId = $fields['evidenceId'];
        $this->fileList = $fields['fileList'] ?? [];
    }

    /**
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'alarmId' => $this->getAlarmId(),
            'evidenceId' => $this->getEvidenceId(),
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
    public function getEvidenceId(): ?string
    {
        return $this->evidenceId;
    }

    /**
     * @return array|StreamaxFile[]
     */
    public function getFileList(): array
    {
        return $this->fileList;
    }
}

