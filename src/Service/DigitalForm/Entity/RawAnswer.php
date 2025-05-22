<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity;

class RawAnswer
{
    /** @var array */
    private $data;

    /** @var array */
    private $files;

    /** @var array */
    private $answers;


    public function __construct(array $data, array $files)
    {
        $this->data = $data;
        $this->files = $files;
    }

    public function getData(): array
    {
        if ($this->answers !== null) {
            return $this->answers;
        }

        $this->answers = [];
        $this->compile($this->data);
        $this->compile($this->files);

        return $this->answers;
    }

    private function compile($rows): void
    {
        foreach ($rows as $id => $item) {
            if (empty($this->answers[$id])) {
                $this->answers[$id] = [];
            }

            foreach ($item as $key => $value) {
                $this->answers[$id][$key] = $value;
            }
        }
    }
}
