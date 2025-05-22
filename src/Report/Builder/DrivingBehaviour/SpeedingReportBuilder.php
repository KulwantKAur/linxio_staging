<?php


namespace App\Report\Builder\DrivingBehaviour;

use App\Entity\BaseEntity;
use App\Entity\Speeding;
use App\Entity\User;
use App\Enums\EntityFields;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\Speeding\SpeedingService;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use App\Util\RequestFilterResolver\RequestFilterResolver;
use App\Util\TranslateHelper;

class SpeedingReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_SPEEDING;

    public function getJson()
    {
        return $this->getPaginatedSpeeding();
    }

    public function getPdf()
    {
        return $this->prepareExportData($this->getPaginatedSpeeding());
    }

    public function getCsv()
    {
        return $this->prepareExportData($this->getPaginatedSpeeding());
    }

    public function getPaginatedSpeeding()
    {
        $resolvedParams = RequestFilterResolver::resolve($this->params);
        $params = $resolvedParams + $this->params;

        $vehicles = $this->vehicleService->vehicleList($this->getElasticSearchParams($params), $this->user, false);

        $params['vehicles'] = $vehicles;

        return $this->getSpeedingByParams($params);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getSpeedingByParams($params)
    {
        return $this->emSlave->getRepository(Speeding::class)->getSpeedingByParams($params);
    }

    public function prepareExportData($qb): array
    {
        $data = ArrayHelper::keysToCamelCase($qb->execute()->fetchAll());
        $results = [];
        $fields = $this->params['fields'] ?? [];
        foreach ($data as $result) {
            foreach ($result as $key => $value) {
                if (in_array($key, SpeedingService::FIELDS_IN_METRES)) {
                    if ($value) {
                        $result[$key] = MetricHelper::metersToKm($value);
                    }
                }
            }
            $area = $this->formatReportDate($result, $this->user);

            if (in_array(EntityFields::STARTED_AT, $fields)) {
                $fields[] = EntityFields::STARTED_AT_DATE;
                $fields[] = EntityFields::STARTED_AT_TIME;
                $fields = ArrayHelper::removeFromArrayByValue(EntityFields::STARTED_AT, $fields);
            }
            if (in_array(EntityFields::FINISHED_AT, $fields)) {
                $fields[] = EntityFields::FINISHED_AT_DATE;
                $fields[] = EntityFields::FINISHED_AT_TIME;
                $fields = ArrayHelper::removeFromArrayByValue(EntityFields::FINISHED_AT, $fields);
            }

            $results[] = $area;
        }

        return TranslateHelper::translateEntityArrayForExport($results, $this->translator, $fields, Speeding::class);
    }

    private function formatReportDate(array $route, User $user)
    {
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted();
        $timeFormat = $user->getTimeFormatSetting();

        if (isset($route[EntityFields::STARTED_AT])) {
            $date = DateHelper::formatDate(
                $route[EntityFields::STARTED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate($route[EntityFields::STARTED_AT], $timeFormat, $timeZone);
            $route = ArrayHelper::arraySpliceAfterKey($route, EntityFields::STARTED_AT,
                [EntityFields::STARTED_AT_DATE => $date, EntityFields::STARTED_AT_TIME => $time]);
        }

        if (isset($route[EntityFields::FINISHED_AT])) {
            $date = DateHelper::formatDate(
                $route[EntityFields::FINISHED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate(
                $route[EntityFields::FINISHED_AT], $timeFormat, $timeZone
            );
            $route = ArrayHelper::arraySpliceAfterKey($route, EntityFields::FINISHED_AT,
                [EntityFields::FINISHED_AT_DATE => $date, EntityFields::FINISHED_AT_TIME => $time]);
        }

        return $route;
    }

    private function getElasticSearchParams(array $params): array
    {
        return array_intersect_key($params, array_flip(['defaultLabel', 'regNo', 'depot', 'groups']));
    }
}