<?php

namespace App\Report\Builder\Area;

use App\Entity\Area;
use App\Entity\BaseEntity;
use App\Entity\User;
use App\Enums\EntityFields;
use App\Service\Area\AreaService;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\TranslateHelper;
use Doctrine\ORM\Query;
use Symfony\Contracts\Translation\TranslatorInterface;

class AreaReportHelper
{
    public static function prepareExportData($qb, $requestData, User $user, TranslatorInterface $translator)
    {
        if ($qb instanceof Query) {
            $data = ArrayHelper::keysToCamelCase($qb->getResult());
        } else {
            $data = ArrayHelper::keysToCamelCase($qb->execute()->fetchAll());
        }
        $results = [];
        $fields = $requestData['fields'] ?? [];

        foreach ($data as $area) {
            foreach ($area as $key => $value) {
                if (in_array($key, AreaService::FIELDS_IN_SECONDS)) {
                    $area[$key] = DateHelper::seconds2period($value);
                }
            }
            $area = self::formatReportDate($area, $user);

            if (in_array(EntityFields::ARRIVED_AT, $fields)) {
                $fields[] = EntityFields::ARRIVED_AT_DATE;
                $fields[] = EntityFields::ARRIVED_AT_TIME;
                $fields = ArrayHelper::removeFromArrayByValue(EntityFields::ARRIVED_AT, $fields);
            }
            if (in_array(EntityFields::DEPARTED_AT, $fields)) {
                $fields[] = EntityFields::DEPARTED_AT_DATE;
                $fields[] = EntityFields::DEPARTED_AT_TIME;
                $fields = ArrayHelper::removeFromArrayByValue(EntityFields::DEPARTED_AT, $fields);
            }

            $results[] = $area;
        }

        return TranslateHelper::translateEntityArrayForExport($results, $translator, $fields, Area::class);
    }

    public static function formatReportDate(array $area, User $user)
    {
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted();
        $timeFormat = $user->getTimeFormatSetting();

        if (isset($area[EntityFields::ARRIVED_AT])) {
            $date = DateHelper::formatDate(
                $area[EntityFields::ARRIVED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate($area[EntityFields::ARRIVED_AT], $timeFormat, $timeZone);
            $area = ArrayHelper::arraySpliceAfterKey($area, EntityFields::ARRIVED_AT,
                [EntityFields::ARRIVED_AT_DATE => $date, EntityFields::ARRIVED_AT_TIME => $time]);
        }
        
        if (isset($area[EntityFields::DEPARTED_AT])) {
            $date = DateHelper::formatDate(
                $area[EntityFields::DEPARTED_AT], $dateFormat, $timeZone
            );
            $time = DateHelper::formatDate($area[EntityFields::DEPARTED_AT], $timeFormat, $timeZone);
            $area = ArrayHelper::arraySpliceAfterKey($area, EntityFields::DEPARTED_AT,
                [EntityFields::DEPARTED_AT_DATE => $date, EntityFields::DEPARTED_AT_TIME => $time]);
        }

        return $area;
    }
}