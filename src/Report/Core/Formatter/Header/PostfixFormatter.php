<?php

namespace App\Report\Core\Formatter\Header;

class PostfixFormatter implements HeaderFormatterInterface
{
    public function __construct(
        private readonly array $fields,
        private readonly ?HeaderFormatterInterface $prevFormatter = null
    ) {
    }

    public function format($item): array
    {
        $data = [];
        foreach ($item as $key => $value) {
            $data[$key] = in_array($key, array_keys($this->fields)) ? $value . $this->fields[$key] : $value;
        }

        if ($this->prevFormatter) {
            return $this->prevFormatter->format($data);
        } else {
            return $data;
        }
    }
}