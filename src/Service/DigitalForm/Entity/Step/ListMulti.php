<?php

declare(strict_types=1);

namespace App\Service\DigitalForm\Entity\Step;

use App\Service\DigitalForm\DigitalFormStepFactory;

class ListMulti extends AbstractStep implements \JsonSerializable
{
    /** @var array */
    private $items = [];

    /** @var array|null */
    private $failIndexes;


    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return DigitalFormStepFactory::TYPE_LIST_MULTI;
    }

    /**
     * @inheritDoc
     */
    public function fromArray(array $options)
    {
        $failIndexes = [];
        if ($options['failIndexes'] ?? null) {
            foreach ((array)$options['failIndexes'] as $failIndex) {
                $failIndexes[] = (int)$failIndex;
            }
        }

        $items = [];
        foreach ((array)$options['items'] as $item) {
            $items[] = [
                'label' => (string)$item['label'],
                'index' => (int)$item['index'],
            ];
        }

        $this->items = $items;
        $this->failIndexes = $failIndexes;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function setFailIndexes(array $failIndexes = null): void
    {
        $this->failIndexes = $failIndexes;
    }

    public function getFailIndexes(): ?array
    {
        return $this->failIndexes;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'items' => $this->items,
            'failIndexes' => $this->failIndexes,
        ];
    }
}
