<?php

namespace App\Service\TrackerProvider;

use App\Entity\Device;
use App\Entity\Vehicle;
use App\Service\BaseService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TrackerProviderService extends BaseService
{
    /** @var EntityManager $em */
    public $em;
    public $eventDispatcher;
    public $notificationDispatcher;
    public $logger;
    public $httpClient;
    public $httpClient2;

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        private string $trackerProviderUrl,
        private string $trackerProvider2Url,
        private string $trackerProviderSecret
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->httpClient = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $trackerProviderSecret
            ],
            'base_uri' => $trackerProviderUrl,
            'timeout' => 0.5,
        ]);
        // @todo uncomment for future with high load
        $this->httpClient2 = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $trackerProviderSecret
            ],
            'base_uri' => $trackerProvider2Url,
            'timeout' => 0.5,
        ]);
    }

    /**
     * @param string $urlPath
     * @param string $action
     * @param array|null $params
     * @return int|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request(string $urlPath, string $action = 'GET', ?array $params = []): ?int
    {
        try {
            $response = $this->httpClient->request($action, $urlPath, $params);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['name' => self::class, 'provider' => 1]);
        }

        // @todo join both in future
        try {
            $response2 = $this->httpClient2->request($action, $urlPath, $params);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['name' => self::class, 'provider' => 2]);
        }

        return isset($response) ? $response?->getStatusCode() : null;
    }

    /**
     * @param Device $device
     * @param array $trackerHistoryData
     * @param array $trackerHistoryLast
     * @return int|null
     * @throws \Exception
     */
    public function trackerPositionNotification(Device $device, array $trackerHistoryData, array $trackerHistoryLast)
    {
        $response = $this->request('/tracker/position', 'POST', ['json' => [
            'vendor' => $device->getVendorName(),
            'deviceId' => $device->getId(),
            'device' => $device->toArray(['id', 'status']),
            'vehicleId' => $device->getVehicleId(),
            'imei' => $device->getImei(),
            'clientId' => $device->getClientId(),
            'trackerHistoryLast' => $trackerHistoryLast,
            'trackerHistoryData' => $trackerHistoryData,
        ]]);

        return $response;
    }

    /**
     * @param Device $device
     * @param array $eventsData
     * @return int|null
     * @throws \Exception
     */
    public function trackerEventNotification(Device $device, array $eventsData)
    {
        $response = $this->request('/tracker/event', 'POST', ['json' => [
            'vendor' => $device->getVendorName(),
            'deviceId' => $device->getId(),
            'vehicleId' => $device->getVehicleId(),
            'imei' => $device->getImei(),
            'clientId' => $device->getClientId(),
            'eventsData' => $eventsData
        ]]);

        return $response;
    }

    public function driverChangeNotification(Vehicle $vehicle)
    {
        $response = $this->request('/fleet/notification', 'POST', ['json' => [
            'clientId' => $vehicle->getClientId(),
            'teamId' => $vehicle->getTeam()->getId(),
            'vehicleId' => $vehicle->getId(),
            'driverId' => $vehicle->getDriverId(),
        ]]);

        return $response;
    }

    /**
     * @param array $data
     * @return int|null
     * @throws \Exception
     */
    public function webNotification(array $data)
    {
        $response = $this->request('/notifications/notification', 'POST', ['json' => $data]);

        return $response;
    }
}
