<?php

namespace App\Service\Streamax\Consumer;

class StreamaxQueueMessage
{
    public function __construct(
        public array $data,
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
                'streamaxLogId' => $this->streamaxLogId,
            ]
        );
    }
}
