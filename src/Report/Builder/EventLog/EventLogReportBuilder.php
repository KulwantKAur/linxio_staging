<?php

namespace App\Report\Builder\EventLog;

use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventLogReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_EVENT_LOG;

    public function getJson()
    {
//        $pagination = $this->paginator->paginate($this->generateData(), $this->page, $this->limit);
//        $totalData = $this->billingService->getTotalData($pagination->getItems());
//        $pagination->setItems($this->billingService->updateBillingItemsFormat($pagination->getItems()));
//
//        return new DataWithTotal($pagination, $totalData);
    }

    public function getPdf()
    {
        return $this->generateData();
    }

    public function getCsv()
    {
        return $this->generateData();
    }

    private function generateData(): array
    {
        $logEvent = $this->reportFacade->findBy(
            $this->eventLogReportService->prepareParams($this->params, $this->user)
        );

        if (null === $logEvent) {
            throw new NotFoundHttpException();
        }

        return $this->eventLogReportService->getEventLogSqlExportData(
            $logEvent,
            $this->params,
            $this->user
        );
    }
}