<?php

namespace App\Service\Billing\DTO;

use App\Entity\User;
use App\Util\StringHelper;
use Carbon\Carbon;

class BillingInfoDTO
{
    public string $startDate;
    public string $endDate;
    public int|array|null $teamId;
    public string $order;
    public ?string $sort;
    public ?string $clientName = null;
    public ?string $period = null;
    public int $page = 1;
    public int $limit = 10;

    public function __construct(array $params, User $currentUser)
    {
        $this->startDate = $params['startDate'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $params['endDate'] ?? Carbon::parse($this->startDate)->endOfMonth()->format('Y-m-d');
        $this->teamId = $currentUser->getTeamIdWithAccess();

        if (isset($params['teamId'])) {
            $teamId = is_array($params['teamId']) ? $params['teamId'] : [$params['teamId']];
            $this->teamId = array_intersect($teamId, $this->teamId);
        }

        if ($params['client_name'] ?? null) {
            $this->clientName = $params['client_name'];
        }

        $this->period = $params['period'] ?? null;

        $this->page = $params['page'] ?? 1;
        $this->limit = $params['limit'] ?? 10;

        $this->order = StringHelper::getOrder($params);
        $this->sort = StringHelper::getSort($params, 'team_id');
    }
}