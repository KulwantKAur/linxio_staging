<?php

namespace App\Tests\Behat\Context\Traits;


use App\Entity\InspectionForm;
use App\Entity\Team;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait InspectionFormTrait
{
    protected $inspectionFormData;

    /**
     * @When I want fill inspection form
     */
    public function iWantFillInspectionForm()
    {
        $this->post(
            '/api/inspection-form/1/vehicle/' . $this->vehicleData->id . '/fill',
            $this->fillData,
            [
                'CONTENT_TYPE' => 'multipart/form-data',
            ],
            $this->files
        );

        $this->inspectionFormData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want get inspection form list
     */
    public function iWantGetInspectionFormList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/inspection-form/list?' . $params);
    }

    /**
     * @When I want get inspection form by saved id
     */
    public function iWantGetInspectionFormBySavedId()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/inspection-form/filled/' . $this->inspectionFormData->id);
    }

    /**
     * @When I want get inspection form template for saved vehicleId
     */
    public function iWantGetInspectionFormForSavedVehicleId()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/inspection-form?' . $params);
    }

    /**
     * @When I want set saved team to inspection form
     */
    public function iWantSetSavedTeamToInspectionForm()
    {
        $em = $this->getEntityManager();
        $form = $em->getRepository(InspectionForm::class)->findOneBy([]);
        $team = $em->getRepository(Team::class)->find($this->clientData->team->id);
        $form->addTeam($team);
        $em->flush();
    }

    /**
     * @When I want fill inspection form fields key :key with :field field with :value
     */
    public function iWantFillFields($key, $field, $value)
    {
        if (isset($this->fillData['fields'][$key])) {
            $this->fillData['fields'][$key][$field] = $value;
        } else {
            $this->fillData['fields'][$key] = [$field => $value];
        }
    }

    /**
     * @When I want upload field key :key file
     */
    public function iWantUploadFieldFile($key)
    {
        $this->files['fields'][$key]['file'] = new UploadedFile(
            '/srv/features/files/test_image.png',
            'test_file.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );
    }

    /**
     * @When I want upload sign file
     */
    public function iWantUploadSignFile()
    {
        $this->files['files']['sign'] = new UploadedFile(
            '/srv/features/files/test_image.png',
            'test_file.png',
            'image/png',
            true
        );
    }
}
