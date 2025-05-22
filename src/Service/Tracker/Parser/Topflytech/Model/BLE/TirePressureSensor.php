<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\BLE;

/**
 * @todo
 *
 * @example
 */
class TirePressureSensor extends BaseDataCode
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {

    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        return new self([]);
    }

    /**
     * @inheritDoc
     */
    public function getDataArray(): ?array
    {
        // TODO: Implement getDataArray() method.
    }
}
