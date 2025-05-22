<?php

namespace App\Service\Tracker\Stream;

use App\Entity\Device;
use App\Entity\DeviceCameraEvent;
use App\Entity\DeviceCameraEventFile;
use App\Entity\DeviceVendor;
use App\Entity\User;
use App\Service\BaseService;
use App\Service\Device\DeviceStreamService;
use App\Service\User\UserServiceHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class TrackerStreamService extends BaseService
{
    /**
     * @param EntityManager $em
     */
    public function __construct(
        private PaginatorInterface $paginator,
        protected EntityManager $em,
    ) {
    }

    /**
     * @param Device $device
     * @return string|null
     */
    public function getVideoLink(Device $device): ?string
    {
        return null;
    }

    /**
     * @param Device $device
     * @return array|null
     */
    public function getStreamData(Device $device): ?array
    {
        return [
            $this->getOutwardStreamData($device),
            $this->getDMSStreamData($device),
            $this->getStreamDataByType($device, DeviceStreamService::TYPE_3),
            $this->getStreamDataByType($device, DeviceStreamService::TYPE_4),
            $this->getStreamDataByType($device, DeviceStreamService::TYPE_5),
            $this->getStreamDataByType($device, DeviceStreamService::TYPE_6),
        ];
    }

    /**
     * @param Device $device
     * @return array|null
     */
    public function getOutwardStreamData(Device $device): ?array
    {
        return (new StreamData(DeviceStreamService::TYPE_OUTWARD))->toArray();
    }

    /**
     * @param Device $device
     * @return array|null
     */
    public function getDMSStreamData(Device $device): ?array
    {
        return (new StreamData(DeviceStreamService::TYPE_DMS))->toArray();
    }

    /**
     * @param Device $device
     * @param string $type
     * @return array|null
     */
    public function getStreamDataByType(Device $device, string $type): ?array
    {
        return (new StreamData($type))->toArray();
    }

    /**
     * @param array $params
     * @param User $currentUser
     * @return PaginationInterface
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getCameraEvents(array $params, User $currentUser): PaginationInterface
    {
        $params = UserServiceHelper::handleTeamParams($params, $currentUser);
        $dateFrom = isset($params['dateFrom']) ? self::parseDateToUTC($params['dateFrom']) : Carbon::now()->subDay();
        $dateTo = isset($params['dateTo']) ? self::parseDateToUTC($params['dateTo']) : Carbon::now();
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $sort = isset($params['sort']) ? ltrim($params['sort'], ' -') : 'createdAt';
        $order = isset($params['sort']) && strpos($params['sort'], '-') !== 0 ? Criteria::ASC : Criteria::DESC;
        $query = $this->em->getRepository(DeviceCameraEvent::class)
            ->getAllByRangeQuery($dateFrom, $dateTo, $params, $sort, $order);
        $pagination = $this->paginator->paginate(
            $query,
            $page,
            ($limit == 0) ? 1 : $limit,
            ['sortFieldParameterName' => '~']
        );

        return $pagination;
    }

    /**
     * @param array $params
     * @param User $currentUser
     * @return PaginationInterface
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getCamerasHistory(array $params, User $currentUser): PaginationInterface
    {
        $params = UserServiceHelper::handleTeamParams($params, $currentUser);
        $dateFrom = isset($params['dateFrom']) ? self::parseDateToUTC($params['dateFrom']) : Carbon::now()->subDay();
        $dateTo = isset($params['dateTo']) ? self::parseDateToUTC($params['dateTo']) : Carbon::now();
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $sort = isset($params['sort']) ? ltrim($params['sort'], ' -') : 'createdAt';
        $order = isset($params['sort']) && strpos($params['sort'], '-') !== 0 ? Criteria::ASC : Criteria::DESC;
        $query = $this->em->getRepository(DeviceCameraEventFile::class)
            ->getAllByRangeQuery($dateFrom, $dateTo, $params, $sort, $order);
        $pagination = $this->paginator->paginate(
            $query,
            $page,
            ($limit == 0) ? 1 : $limit,
            ['sortFieldParameterName' => '~']
        );

        return $pagination;
    }

    /**
     * @param Device $device
     * @return bool
     */
    public function wakeupDevice(Device $device): bool
    {
        return false;
    }

    /**
     * @param Device $device
     * @return bool
     */
    public static function hasCameras(Device $device): bool
    {
        return in_array($device->getVendorName(), [
            DeviceVendor::VENDOR_STREAMAX
        ]);
    }

    /**
     * @param Device $device
     * @param string $text
     * @return bool
     */
    public function sendTTSToDevice(Device $device, string $text): bool
    {
        return false;
    }
}
