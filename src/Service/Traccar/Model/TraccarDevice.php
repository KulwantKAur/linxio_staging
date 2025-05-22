<?php

namespace App\Service\Traccar\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;

class TraccarDevice extends TraccarModel implements ImeiInterface
{
    /** @var int $id */
    public $id;
    /** @var string $uniqueId */
    public $uniqueId;
    /** @var string|null $phone */
    public $phone;
    /** @var string|null $model */
    public $model;
    /** @var string|null $contact */
    public $contact;
    /** @var string|null $lastUpdate */
    public $lastUpdate;
    /** @var string|null $status */
    public $status;
    /** @var string|null $name */
    public $name;
    /** @var bool|null $disabled */
    public $disabled;

    /**
     * @param \stdClass $fields
     */
    public function __construct(\stdClass $fields)
    {
        $fields = self::convertArrayToObject($fields);
        $this->id = $fields->id ?? null;
        $this->uniqueId = $fields->uniqueId ?? null;
        $this->phone = $fields->phone ?? null;
        $this->model = $fields->model ?? null;
        $this->contact = $fields->contact ?? null;
        $this->lastUpdate = $fields->lastUpdate ?? null;
        $this->status = $fields->status ?? null;
        $this->name = $fields->name ?? null;
        $this->disabled = $fields->disabled ?? null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @return string|null
     */
    public function getContact(): ?string
    {
        return $this->contact;
    }

    /**
     * @return string|null
     */
    public function getLastUpdate(): ?string
    {
        return $this->lastUpdate;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getImei(): string
    {
        return $this->getUniqueId();
    }

    /**
     * @inheritDoc
     */
    public function getProtocol(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getRawAttributes(): ?\stdClass
    {
        return null;
    }

    /**
     * @return bool|null
     */
    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return boolval($this->getDisabled() === true);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return boolval($this->getDisabled() === false);
    }

    /**
     * @param bool|null $disabled
     */
    public function setDisabled(?bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    /**
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'id' => $this->getId(),
            'uniqueId' => $this->getUniqueId(),
            'disabled' => $this->getDisabled(),
            'name' => $this->getName(),
            'phone' => $this->getPhone(),
            'model' => $this->getModel(),
            'contact' => $this->getContact(),
            'lastUpdate' => $this->getLastUpdate(),
            'status' => $this->getStatus(),
        ];
    }
}

