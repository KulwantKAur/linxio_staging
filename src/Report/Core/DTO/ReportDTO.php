<?php

namespace App\Report\Core\DTO;

class ReportDTO
{
    public array $header;
    public array $data;

    /**
     * ReportDTO constructor.
     * @param array $header
     * @param array $data
     */
    public function __construct(array $header, array $data)
    {
        $this->header = $header;
        $this->data = $data;
    }

    /**
     * @param array $header
     * @return array
     */
    public function setHeader(array $header): array
    {
        return $this->header = $header;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param array $data
     * @return array
     */
    public function setData(array $data): array
    {
        return $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
