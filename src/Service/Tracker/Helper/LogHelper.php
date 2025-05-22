<?php

namespace App\Service\Tracker\Helper;

use App\Entity\BaseEntity;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Util\DateHelper;
use App\Util\TranslateHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogHelper
{
    public static function formatDataToCsv(array $data, User $user, TranslatorInterface $translator, $fields = []): array
    {
        $results = [];

        foreach ($data as $key => $item) {
            $newItem = [];
            $newItem['createdAt'] = DateHelper::formatDate(
                $item['createdAt'], BaseEntity::EXPORT_DATE_FORMAT, $user->getTimezone()
            );
            $newItem['ts'] = DateHelper::formatDate(
                $item['ts'], BaseEntity::EXPORT_DATE_FORMAT, $user->getTimezone()
            );
            $newItem['gps'] = isset($item['lat']) && isset($item['lng']) ? $item['lat'] . ' ' . $item['lng'] : null;
            $newItem['speed'] = $item['speed'] ?? null;
            $newItem['angle'] = $item['angle'] ?? null;
            $newItem['batteryVoltage'] = isset($item['batteryVoltage']) && (floor($item['batteryVoltage']) > 100)
                ? round($item['batteryVoltage'] / 1000, 2)
                : $item['batteryVoltage'];
            $newItem['batteryVoltagePercentage'] = $item['batteryVoltagePercentage'] ?? null;
            $newItem['temperatureLevel'] = isset($item['temperatureLevel']) ? $item['temperatureLevel'] / 1000 : null;
            $newItem['odometer'] = isset($item['odometer']) ? round($item['odometer'] / 1000, 1) : null;
            $newItem['externalVoltage'] = isset($item['externalVoltage'])
                ? round($item['externalVoltage'] / 1000, 2)
                : null;

            $newItem['io'] = null;
            if (isset($item['extraData']['IOData'])) {
                $newItem['io'] = json_encode($item['extraData']['IOData']);
            }
            if (isset($item['ioFromExtraData']['IOData'])) {
                $newItem['io'] = json_encode($item['ioFromExtraData']['IOData']);
            }

            unset($data[$key]);
            $results[] = $newItem;
        }

        return TranslateHelper::translateEntityArrayForExport($results, $translator, $fields, TrackerHistory::class);
    }
}