<?php

namespace App\Service\Route;

use App\Entity\BaseEntity;
use App\Entity\Route;
use App\Entity\User;
use App\Enums\EntityFields;
use App\Enums\SqlEntityFields;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;

trait RouteServiceFieldsTrait
{
    /**
     * @param array $route
     * @param User $user
     * @return array
     * @throws \Exception
     */
    private function formatReportFields(array $route, User $user)
    {
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted();

        foreach ($route as $key => $value) {
            if (in_array($key, self::FIELDS_IN_SECONDS)) {
                $route[$key] = DateHelper::seconds2period($value);
            }
            if (in_array($key, self::FIELDS_IN_METRES)) {
                if ($value) {
                    $route[$key] = MetricHelper::metersToKm($value);
                }
            }
        }

        if (isset($route[SqlEntityFields::STARTED_AT])) {
            $date = DateHelper::formatDate(
                $route[SqlEntityFields::STARTED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate(
                $route[SqlEntityFields::STARTED_AT], BaseEntity::EXPORT_TIME_FORMAT, $timeZone
            );
            $route = ArrayHelper::arraySpliceAfterKey($route, SqlEntityFields::STARTED_AT,
                [SqlEntityFields::STARTED_AT_DATE => $date, SqlEntityFields::STARTED_AT_TIME => $time]);
        }

        if (isset($route[SqlEntityFields::FINISHED_AT])) {
            $date = DateHelper::formatDate(
                $route[SqlEntityFields::FINISHED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate(
                $route[SqlEntityFields::FINISHED_AT], BaseEntity::EXPORT_TIME_FORMAT, $timeZone
            );
            $route = ArrayHelper::arraySpliceAfterKey($route, SqlEntityFields::FINISHED_AT,
                [SqlEntityFields::FINISHED_AT_DATE => $date, SqlEntityFields::FINISHED_AT_TIME => $time]);
        }

        if (isset($route['defaultlabel'])) {
            $route = array_merge(['defaultLabel' => $route['defaultlabel']], $route);
            unset($route['defaultlabel']);
        }

        return $route;
    }

    public function prepareExportData($routes, $requestData, User $user, $totalFields = [])
    {
        $results = [];
        $routes = $routes->execute()->fetchAll();
        $fields = array_merge($requestData['fields'] ?? [], $totalFields);

        foreach ($routes as $route) {
            $results[] = $this->formatReportFields($route, $user);
        }
        if (in_array(SqlEntityFields::STARTED_AT, $fields)) {
            $fields[] = SqlEntityFields::STARTED_AT_DATE;
            $fields[] = SqlEntityFields::STARTED_AT_TIME;
            $fields = ArrayHelper::removeFromArrayByValue(SqlEntityFields::STARTED_AT, $fields);
        }
        if (in_array(SqlEntityFields::FINISHED_AT, $fields)) {
            $fields[] = SqlEntityFields::FINISHED_AT_DATE;
            $fields[] = SqlEntityFields::FINISHED_AT_TIME;
            $fields = ArrayHelper::removeFromArrayByValue(SqlEntityFields::FINISHED_AT, $fields);
        }

        return $this->translateEntityArrayForExport($results, $fields, Route::class);
    }
}