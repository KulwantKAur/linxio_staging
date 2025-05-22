<?php

namespace App\Service\Tracker\Command;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;

abstract class TrackerCommand implements TrackerCommandInterface
{
    /** @var string $actionType */
    private $actionType;
    /** @var Device $device */
    private $device;
    /** @var string $protocol */
    private $protocol;

    /**
     * @return int|null
     */
    abstract function getType(): ?int;

    /**
     * @param Device $device
     * @param string $actionType
     */
    public function __construct(Device $device, string $actionType = TrackerCommandService::ADD_ACTION_TYPE)
    {
        $this->device = $device;
        $this->actionType = $actionType;
    }

    /**
     * @inheritDoc
     */
    public function sendCommand(string $command)
    {
        // TODO: Implement sendCommand() method.
    }

    /**
     * @inheritDoc
     */
    public function getCommand(): ?string
    {
        switch ($this->getActionType()) {
            case TrackerCommandService::DELETE_ACTION_TYPE:
                return static::getDeleteCommand();
            case TrackerCommandService::LIST_ACTION_TYPE:
                return static::getListCommand();
            case TrackerCommandService::SET_ACTION_TYPE:
                return static::getSetCommand();
            case TrackerCommandService::GET_ACTION_TYPE:
                return static::getGetCommand();
            default:
                return static::getAddCommand();
        }
    }

    /**
     * @return string
     */
    public function getActionType(): string
    {
        return $this->actionType;
    }

    /**
     * @param string $actionType
     */
    public function setActionType(string $actionType): void
    {
        $this->actionType = $actionType;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @return string|null
     */
    public function getAddCommand(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getDeleteCommand(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getListCommand(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getSetCommand(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getGetCommand(): ?string
    {
        return null;
    }
}