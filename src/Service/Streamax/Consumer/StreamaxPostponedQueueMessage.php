<?php

namespace App\Service\Streamax\Consumer;

class StreamaxPostponedQueueMessage
{
    public function __construct(
        public array  $data,
        public string $type,
        public ?int $streamaxLogId = null,
    ) {

    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'data' => $this->data,
                'type' => $this->type,
                'streamaxLogId' => $this->streamaxLogId,
            ]
        );
    }
}
