<?php

namespace App\Report\Builder\Billing;

use App\Report\Core\DataType\DataWithTotal;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use Doctrine\DBAL\Query\QueryBuilder;

class BillingPaymentsReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_BILLING;

    public function getJson()
    {
        $pagination = $this->paginator->paginate($this->generateData(), $this->page, $this->limit);
        $totalData = $this->billingService->getTotalData($pagination->getItems());
        $pagination->setItems($this->billingService->updateBillingItemsFormat($pagination->getItems()));

        return new DataWithTotal($pagination, $totalData);
    }

    public function getPdf()
    {
        $data = $this->generateData()->execute()->fetchAllAssociative();

        return $this->billingService->prepareExportData($data, $this->params['fields'] ?? []);
    }

    public function getCsv()
    {
        $data = $this->generateData()->execute()->fetchAllAssociative();

        return $this->billingService->prepareExportData($data, $this->params['fields'] ?? []);
    }

    private function generateData(): QueryBuilder
    {
        return $this->billingService->getClientsBillingPayments($this->params, $this->user);
    }
}