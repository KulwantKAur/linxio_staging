<?php

namespace App\Service\MapService;

use App\Service\MapService\MapBox\MapBoxService;

class MapServiceResolver
{
    const MAPBOX = 'mapbox';
    const GOOGLE = 'google';

    public function __construct(
        private readonly MapBoxService $mapBoxService
    ) {
    }

    public function getInstance(): MapServiceInterface
    {
        // todo implement logic to use right map service for client
        $mapServiceName = self::MAPBOX;

        return match ($mapServiceName) {
            self::MAPBOX => $this->mapBoxService,
            default => throw new \Exception('Unsupported map service name: ' . $mapServiceName),
        };
    }
}