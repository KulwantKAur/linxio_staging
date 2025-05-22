<?php

namespace App\Service\Tracker\Stream;

use App\Util\DateHelper;

class StreamData
{
    /**
     * @param string|null $type
     * @param string|null $url
     * @param bool $isAvailable
     * @param \DateTimeInterface|null $expiredAt
     */
    public function __construct(
        private ?string             $type = null,
        private ?string             $url = null,
        private bool                $isAvailable = false,
        private ?\DateTimeInterface $expiredAt = null,
    ) {
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'url' => $this->getUrl(),
            'isAvailable' => $this->isAvailable(),
            'expiredAt' => $this->getExpiredAt() ? DateHelper::formatDate($this->getExpiredAt()) : null,
        ];
    }
}
