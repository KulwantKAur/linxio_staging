<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\BaseEntity;
use App\Entity\ServiceRecord;
use App\Entity\User;
use App\Enums\SqlEntityFields;
use App\Service\BaseService;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\StringHelper;
use App\Util\TranslateHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaintenanceReportHelper
{
    /**
     * @param array $data
     * @return array
     */
    public static function prepareFields(array $data)
    {
        $data['startDate'] =
            isset($data['startDate']) ? BaseService::parseDateToUTC($data['startDate']) : Carbon::now();
        $data['endDate'] = isset($data['endDate'])
            ? BaseService::parseDateToUTC($data['endDate'])
            : (new Carbon())->subHours(24);

        $data['defaultLabel'] = $data['defaultLabel'] ?? null;
        $data['vehicleRegNo'] = $data['regno'] ?? null;
        $data['vehicleGroup'] = $data['groups'] ?? null;
        $data['vehicleDepot'] = $data['depot_name'] ?? null;
        $data['depotId'] = null;
        $data['vehicleIds'] = empty($data['vehicleIds']) ? null : $data['vehicleIds'];
        $data['r_category'] = $data['r_category'] ?? null;
        $data['repairTitle'] = $data['repair_title'] ?? null;
        $data['title'] = $data['title'] ?? null;
        $data['teamId'] = $data['teamId'] ?? null;
        $data['order'] = StringHelper::getOrder($data);
        $data['sort'] = StringHelper::getSort($data, 'defaultLabel');

        if (isset($data['groups']) && is_array($data['groups'])) {
            $data['vehicleGroup'] = implode(', ', $data['groups']);
        }

        if (isset($data['depot'])) {
            if (is_array($data['depot'])) {
                $data['depotId'] = implode(', ', $data['depot']);
            } elseif (StringHelper::isNullString($data['depot'])) {
                $data['depotId'] = $data['depot'];
            }
        }

        return $data;
    }

    public static function prepareExportData(
        $serviceRecords,
        $params,
        User $user,
        TranslatorInterface $translator,
        $totalFields = []
    ): array {
        $results = [];
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted();
        $timeFormat = $user->getTimeFormatSetting();
        $fields = array_merge($params['fields'] ?? [], $totalFields);

        foreach ($serviceRecords as $serviceRecord) {
            if (array_key_exists(SqlEntityFields::SR_DATE, $serviceRecord)) {
                $date = DateHelper::formatDate($serviceRecord[SqlEntityFields::SR_DATE], $dateFormat, $timeZone);
                $time = DateHelper::formatDate(
                    $serviceRecord[SqlEntityFields::SR_DATE], $timeFormat, $timeZone
                );
                $serviceRecord = ArrayHelper::arraySpliceAfterKey($serviceRecord, SqlEntityFields::SR_DATE,
                    [SqlEntityFields::SR_DATE_DATE => $date, SqlEntityFields::SR_DATE_TIME => $time]);
            }

            if (array_key_exists(SqlEntityFields::SR_LAST_DATE, $serviceRecord)) {
                $date = DateHelper::formatDate($serviceRecord[SqlEntityFields::SR_LAST_DATE], $dateFormat, $timeZone);
                $time = DateHelper::formatDate(
                    $serviceRecord[SqlEntityFields::SR_LAST_DATE], $timeFormat, $timeZone
                );
                $serviceRecord = ArrayHelper::arraySpliceAfterKey($serviceRecord, SqlEntityFields::SR_LAST_DATE,
                    [SqlEntityFields::SR_LAST_DATE_DATE => $date, SqlEntityFields::SR_LAST_DATE_TIME => $time]);
            }

            if (isset($serviceRecord['defaultlabel'])) {
                $serviceRecord = array_merge(['defaultLabel' => $serviceRecord['defaultlabel']], $serviceRecord);
                unset($serviceRecord['defaultlabel']);
            }

            $results[] = $serviceRecord;
        }

        if (in_array(SqlEntityFields::SR_DATE, $fields)) {
            $fields[] = SqlEntityFields::SR_DATE_DATE;
            $fields[] = SqlEntityFields::SR_DATE_TIME;
            $fields = ArrayHelper::removeFromArrayByValue(SqlEntityFields::SR_DATE, $fields);
        }
        if (in_array(SqlEntityFields::SR_LAST_DATE, $fields)) {
            $fields[] = SqlEntityFields::SR_LAST_DATE_DATE;
            $fields[] = SqlEntityFields::SR_LAST_DATE_TIME;
            $fields = ArrayHelper::removeFromArrayByValue(SqlEntityFields::SR_LAST_DATE, $fields);
        }

        return TranslateHelper::translateEntityArrayForExport($results, $translator, $fields, ServiceRecord::class);
    }
}