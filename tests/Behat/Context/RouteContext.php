<?php

namespace App\Tests\Behat\Context;

/**
 * Defines application features from the specific context.
 */
class RouteContext extends DepotContext
{
    protected $vehicleData;
    protected $deviceData;
    protected $routeData;

    /**
     * @When I want get vehicle routes
     */
    public function iWantGetVehicleRoutes()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/' . $this->vehicleData->id . '/routes?' . $params);
    }

    /**
     * @When I want get vehicle routes paginated
     */
    public function iWantGetVehicleRoutesPaginated()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/' . $this->vehicleData->id . '/routes/paginated?' . $params);
    }

    /**
     * @When I want get vehicle routes and save first
     */
    public function iWantGetVehicleRoutesAndSaveFirst()
    {
        $params = http_build_query($this->fillData);
        $response = $this->get('/api/vehicles/' . $this->vehicleData->id . '/routes?' . $params);

        if ($response->getResponse()->getStatusCode() === 200) {
            $routes = json_decode($response->getResponse()->getContent());
            $this->routeData = isset($routes[0]->routes[0]) ? $routes[0]->routes[0] : [];
        }
    }

    /**
     * @When I want get route by saved id
     */
    public function iWantGetRouteBySavedId()
    {
        $this->get('/api/routes/' . $this->routeData->id);
    }

    /**
     * @When I want get route by saved id with query params
     */
    public function iWantGetRouteBySavedIdWithParams()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/routes/' . $this->routeData->id . '?' . $params);
    }


    /**
     * @When I want update saved route
     */
    public function iWantUpdateRoute()
    {
        return $this->patch('/api/routes/' . $this->routeData->id, $this->fillData);
    }

    /**
     * @When I want get driver routes
     */
    public function iWantGetDriverRoutes()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/drivers/' . $this->driverId . '/routes?' . $params);
    }

    /**
     * @When I want get driver routes paginated
     */
    public function iWantGetDriverRoutesPaginated()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/drivers/' . $this->driverId . '/routes/paginated?' . $params);
    }

    /**
     * @When I want get vehicles and drivers routes history
     */
    public function iWantGetVehiclesDriversRoutesHistory()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles-drivers/history?' . $params);
    }

    /**
     * @When Map service mapbox is ready to use
     */
    public function iWantHandleMapServiceRequest()
    {
        $mapServiceMock = \Mockery::mock($this->getContainer()->get('app.mapbox_service'));

        $mapServiceMock->shouldReceive('getLocationByCoordinates')
            ->andReturnUsing(
                function ($lat, $lng) {
                    switch ($lat) {
                        default:
                            return 'Partizanskiy rayon, Minsk, City of Minsk, Belarus';
                    }
                }
            );

        $this->getContainer()->set('app.mapbox_service', $mapServiceMock);
    }

    /**
     * @When I want get route report with type :type
     */
    public function iWantGetRouteReport($type)
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/routes-report/' . $type . '?' . $params);
    }

    /**
     * @When I want get fbt report with type :type
     */
    public function iWantGetFbtReport($type)
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/fbt-report/' . $type . '?' . $params);
    }

    /**
     * @When I want get route report vehicle list
     */
    public function iWantGetRouteReportVehicleList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/routes-report/vehicles' . '?' . $params);
    }

    /**
     * @When I want get driver summary report with type :type
     */
    public function iWantGetDriverSummaryReport($type)
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/driver-summary-report/' . $type . '?' . $params);
    }

    /**
     * @When I want get driver summary report driver list
     */
    public function iWantGetDriverSummaryReportDriverList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/driver-summary-drivers/json?' . $params);
    }

    /**
     * @When I want get route stops with type :type
     */
    public function iWantGetRouteStops($type)
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/stops-report/' . $type . '?' . $params);
    }

    /**
     * @When I want get route stops vehicle list
     */
    public function iWantGetRouteStopsVehicleList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/stops-report/vehicles?' . $params);
    }
}
