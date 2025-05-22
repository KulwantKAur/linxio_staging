<?php

namespace App\Util\RequestFilterResolver;

use DateTime;
use App\Util\DateHelper;

class RequestFilterResolver
{
    /**
     * @param array $params
     *
     * @return array
     */
    public static function resolve(array $params): array
    {
        if (isset($params['startDate'])) {
            $startDate = new DateTime($params['startDate']);
        } elseif (isset($params['endDate'])) {
            $startDate = DateHelper::getFirstDayOfMonth(new DateTime($params['startDate']));
        } else {
            $startDate = DateHelper::getFirstDayOfMonth(new DateTime());
        }

        if (isset($params['endDate'])) {
            $endDate = new DateTime($params['endDate']);
        } else {
            $endDate = DateHelper::getLastDayOfMonth($startDate);
        }

        $startDate = (new DateTime())
            ->setTimestamp($startDate->format('U'))
            ->setTimezone(new \DateTimeZone('UTC'));
        $endDate = (new DateTime())
            ->setTimestamp($endDate->format('U'))
            ->setTimezone(new \DateTimeZone('UTC'));

        $resolvedParams['startDate'] = $startDate->format('Y-m-d H:i:s');
        $resolvedParams['endDate'] = $endDate->format('Y-m-d H:i:s');

        if (isset($params['sort'])) {
            $resolvedParams['sort'] = ltrim($params['sort'], ' -');
            $resolvedParams['order'] = strpos($params['sort'], '-') === false ? 'ASC' : 'DESC';
        }

        $resolvedParams['limit'] = $params['limit'] ?? 10;
        $resolvedParams['page'] = $params['page'] ?? 1;

        return $resolvedParams;
    }
}
