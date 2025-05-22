<?php

namespace App\Tests\Behat\Context;

use App\Entity\File;

class FuelCardContext extends VehicleContext
{
    protected $fileData;
    protected $fuelIgnoreData;
    protected $fuelMappingData;

    /**
     * @When I want get fuel card report
     */
    public function iWantGetFuelCardReport()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/fuel-cards/json?' . $params);
    }

    /**
     * @When I want get vehicles by fuel card report
     */
    public function iWantGetFuelCardVehicle()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/fuel-cards/vehicles?' . $params);
    }

    /**
     * @When I want get fuel summary report for elastica
     */
    public function iWantGetFuelSummaryReportForElastica()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/fuel-summary/json?' . $params);
    }

    /**
     * @When I want get fuel summary report for query-db
     */
    public function iWantGetFuelSummaryReport()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/fuel-summary-report/json?' . $params);
    }

    /**
     * @When I want export fuel summary report for query-db
     */
    public function iWantExportFuelSummaryReport()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/fuel-summary-report/csv?' . $params);
    }

    /**
     * @When I want export fuel card report
     */
    public function iWantExportEventLog()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/fuel-cards/csv?' . $params);
    }

    /**
     * @When I want load file and save response
     */
    public function iWantCreateDocument()
    {
        $response = $this->post(
            '/api/fuel-cards/upload',
            $this->fillData,
            ['CONTENT_TYPE' => 'multipart/form-data'],
            ['files' => $this->files]
        );

        if ($response->getResponse()->getStatusCode() === 200) {
            $this->fileData = json_decode(
                $response->getResponse()->getContent()
            );
        }
    }

    /**
     * @When I want delete upload file :file
     */
    public function iWantDeleteUploadFile($file)
    {
        $em = $this->getEntityManager();
        $file = $em->getRepository(File::class)->findOneBy(['displayName' => $file]);

        return $this->delete('/api/fuel-cards/'. $file->getId());
    }

    /**
     * @When I want save upload file :file
     */
    public function iWantSaveUploadFile($file)
    {
        $em = $this->getEntityManager();
        $file = $em->getRepository(File::class)->findOneBy(['displayName' => $file], ['createdAt' => 'DESC']);

        return $this->post('/api/fuel-cards/'. $file->getId());
    }

    /**
     * @When I want get fuel ignore list
     */
    public function iWantGetFuelIgnoreList()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/fuel-ignored?' . $params);
    }

    /**
     * @When I want to create fuel ignore and save id
     */
    public function iWantCreateFuelIgnore()
    {
        $response = $this->post('/api/fuel-ignored', $this->fillData);
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->fuelIgnoreData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to edit fuel ignore by saved id
     */
    public function iWantEditFuelIgnoreBySavedId()
    {
        $this->patch('/api/fuel-ignored/' . $this->fuelIgnoreData->id, $this->fillData);
    }

    /**
     * @When I want to delete fuel ignore by saved id
     */
    public function iWantDeleteFuelIgnoreBySavedId()
    {
        $this->delete('/api/fuel-ignored/' . $this->fuelIgnoreData->id);
    }

    /**
     * @When I want get fuel ignore by saved id
     */
    public function iWantGetFuelIgnoreBySavedId()
    {
        $this->get('/api/fuel-ignored/' . $this->fuelIgnoreData->id);
    }

    /**
     * @When I want get fuel mapping list
     */
    public function iWantGetFuelMappingList()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/fuel-mapping?' . $params);
    }

    /**
     * @When I want to create fuel mapping and save id
     */
    public function iWantCreateFuelMapping()
    {
        $response = $this->post('/api/fuel-mapping', $this->fillData);
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->fuelMappingData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to edit fuel mapping by saved id
     */
    public function iWantEditFuelMappingBySavedId()
    {
        $this->patch('/api/fuel-mapping/' . $this->fuelMappingData->id, $this->fillData);
    }

    /**
     * @When I want to delete fuel mapping by saved id
     */
    public function iWantDeleteFuelMappingBySavedId()
    {
        $this->delete('/api/fuel-mapping/' . $this->fuelMappingData->id);
    }

    /**
     * @When I want get fuel mapping by saved id
     */
    public function iWantGetFuelMappingBySavedId()
    {
        $this->get('/api/fuel-mapping/' . $this->fuelMappingData->id);
    }

    /**
     * @When I want get fuel types
     */
    public function iWantGetFuelTypes()
    {
        $this->get('/api/fuel-types');
    }
}
