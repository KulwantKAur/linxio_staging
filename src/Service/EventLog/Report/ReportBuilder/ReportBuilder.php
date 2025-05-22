<?php

namespace App\Service\EventLog\Report\ReportBuilder;

use App\Entity\EventLog\EventLog;
use App\Entity\User;
use App\Service\EventLog\Interfaces\ReportBuilderInterface;
use App\Service\EventLog\Interfaces\ReportHandlerInterface;

class ReportBuilder implements ReportBuilderInterface
{
    protected ReportHandlerInterface $handler;
    protected array $data = [];
    protected array $header = [];

    /**
     * ReportBuilder constructor.
     * @param ReportHandlerInterface $handler
     */
    public function __construct(ReportHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $columns
     * @return array
     */
    public function setHeader(array $columns = []): array
    {
        foreach ($this->handler->getHeader() as $key => $value) {
            $indexItem = array_search($key, $columns);

            if ($indexItem === false) {
                continue;
            }
            $this->header[$key] = $value;
        }

        return $this->header;
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
     * @param User $user
     * @param array $params
     * @return ReportBuilderInterface
     */
    public function build(array $data, User $user, array $params = []): ReportBuilderInterface
    {
        $shortDetailsKey = array_search(EventLog::SHORT_DETAILS, $params);
        if ($shortDetailsKey !== false) {
            unset($params[$shortDetailsKey]);
        }
        $header = $this->setHeader($params);

        foreach ($data as $key => $eventLog) {
            if ($eventLog) {
                $this->data[$key] = $this->handler->toExport($eventLog, $header);
            }
        }

        return $this;
    }
}
