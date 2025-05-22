<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\Reminder;
use App\Entity\User;
use App\Enums\EntityFields;
use App\Report\Core\ResponseType\ArrayResponse;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Util\MetricHelper;
use App\Util\TranslateHelper;

class MaintenanceSummaryReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_MAINTENANCE_SUMMARY;
    private const FIELDS_IN_METRES = ['remainingMileage', 'controlMileage'];

    public function getJson()
    {
        $this->params['fields'] = array_merge(Reminder::DEFAULT_DISPLAY_VALUES, ['vehicleDetailed']);

        return new ArrayResponse($this->reminderService->reminderList($this->params, $this->user));
    }

    public function getPdf()
    {
        return $this->prepareExportData($this->params, $this->user, false);
    }

    public function getCsv()
    {
        return $this->prepareExportData($this->params, $this->user, false);
    }


    public function prepareExportData($params, User $user, $paginated = false)
    {
        $reminders = $this->reminderService->reminderList($this->params, $this->user, $paginated);
        $fields = $params['fields'];

        if (in_array(EntityFields::LAST_MODIFIED, $fields)) {
            $fields[] = EntityFields::LAST_MODIFIED_DATE;
            $fields[] = EntityFields::LAST_MODIFIED_TIME;
        }
        if (in_array(EntityFields::CREATED_AT, $fields)) {
            $fields[] = EntityFields::CREATED_AT_DATE;
            $fields[] = EntityFields::CREATED_AT_TIME;
        }
        $reminders = array_map(fn(Reminder $reminder) => $reminder->toExport($fields, $user), $reminders);
        $results = [];

        foreach ($reminders as $reminder) {
            foreach ($reminder as $key => $value) {
                if (in_array($key, self::FIELDS_IN_METRES)) {
                    if ($value) {
                        $reminder[$key] = MetricHelper::metersToKm($value);
                    }
                }
            }

            $results[] = $reminder;
        }

        return TranslateHelper::translateEntityArrayForExport(
            $results, $this->translator, $fields, Reminder::class, $user
        );
    }
}