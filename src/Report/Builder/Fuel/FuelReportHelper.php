<?php

namespace App\Report\Builder\Fuel;

use App\Entity\FuelCard\FuelCard;
use App\Entity\User;
use App\Service\BaseService;
use App\Util\TranslateHelper;
use Carbon\Carbon;
use Symfony\Contracts\Translation\TranslatorInterface;

class FuelReportHelper
{
    public static function getPrepareFields(array $params, User $user): array
    {
        $data['startDate'] =
            $params['startDate'] ? BaseService::parseDateToUTC($params['startDate'])->startOfDay() : Carbon::now();
        $data['endDate'] = $params['endDate']
            ? BaseService::parseDateToUTC($params['endDate'])->endOfDay()
            : (new Carbon())->subHours(24);

        $data['vehicleRegNo'] = $params['vehicleRegNo'] ?? null;
        $data['vehicleDepot'] = $params['vehicleDepot'] ?? null;
        $data['vehicleGroups'] = $params['vehicleGroups'] ?? null;
        $data['total'] = $params['total'] ?? null;
        $data['refueled'] = $params['refueled'] ?? null;
        $data['sort'] = $params['sort'] ?? 'refueled';
        $data['order'] = $params['order'] ?? null;
        $data['teamId'] = $params['teamId'] ?? null;

        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $data['teamId'] = [$user->getTeam()->getId()];
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $data['teamId'] = $user->getManagedTeamsIds();
        }

        return $data;
    }

    public static function prepareExportSummaryData($report, $params, TranslatorInterface $translator)
    {
        $results = $report->execute()->fetchAll();

        return TranslateHelper::translateEntityArrayForExport(
            $results,
            $translator,
            $params['fields'] ?? FuelCard::EXPORT_FUEL_SUMMARY,
            FuelCard::class
        );
    }
}
