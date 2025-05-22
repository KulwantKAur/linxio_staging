<?php

namespace App\Service\MapService\MapBox;

use App\Service\MapService\MapServiceInterface;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Provider\Mapbox\Mapbox;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class MapBoxService implements MapServiceInterface
{
    private $mapBoxKey;
    private $geocoder;
    private $logger;

    /**
     * MapBoxService constructor.
     * @param string $mapBoxKey
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, string $mapBoxKey)
    {
        $this->mapBoxKey = $mapBoxKey;
        $httpClient = new Client();
        $provider = new Mapbox($httpClient, $this->mapBoxKey);
        $this->geocoder = new StatefulGeocoder($provider);
        $this->logger = $logger;
    }

    /**
     * @param $lat
     * @param $lng
     * @return string|null
     * @throws \Geocoder\Exception\Exception
     */
    public function getLocationByCoordinates($lat, $lng): ?string
    {
        try {
            $dataCollection = $this->geocoder->reverseQuery(ReverseQuery::fromCoordinates($lat, $lng)
                ->withLimit(1)->withData('location_type', [Mapbox::TYPE_ADDRESS, Mapbox::TYPE_LOCALITY, Mapbox::TYPE_POI, Mapbox::TYPE_PLACE]));
            if (!$dataCollection->isEmpty()) {
                return $dataCollection->first()->getFormattedAddress();
            }

            return null;
        } catch (\Exception $e) {
            $this->saveToLog($e);

            return null;
        }
    }

    /**
     * @param string $location
     * @return array|null
     * @throws \Geocoder\Exception\Exception
     */
    public function getCoordinatesByLocation(string $location): ?array
    {
//        try {
            $result = $this->geocoder->geocodeQuery(GeocodeQuery::create($location));

            if (!$result->isEmpty()) {
                $coordinates = $result->first()->getCoordinates();

                return [
                    'lat' => $coordinates->getLatitude(),
                    'lng' => $coordinates->getLongitude(),
                ];
            }

            return null;
//        } catch (\Exception $e) {
//            $this->saveToLog($e);
//
//            return null;
//        }
    }

    /**
     * @param \Exception $exception
     */
    private function saveToLog(\Exception $exception)
    {
        if ($exception instanceof InvalidCredentials) {
            $this->logger->error('MapBox wrong credentials');
        } else {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception->getTraceAsString());
        }
    }
}