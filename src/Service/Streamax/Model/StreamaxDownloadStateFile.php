<?php

namespace App\Service\Streamax\Model;

/**
 * @todo check if it's the same like StreamaxFile
 */
class StreamaxDownloadStateFile extends StreamaxModel
{
    public ?string $fileId; // evidence file’s id
    public ?string $fileType; // VIDEO, BLACK_BOX
    public ?int $channelNo;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fileId = $fields['fileId'] ?? null;
        $this->fileType = $fields['fileType'] ?? null;
        $this->channelNo = $fields['channelNo'] ?? null;
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
    public function getChannelNo(): ?int
    {
        return $this->channelNo;
    }
}

