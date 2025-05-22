<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\DigitalForm;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait DigitalFormTrait
{
    /** @var array */
    private $digitalFormListData;

    /** @var array */
    private $singleFormData;

    /** @var array */
    private $fillStepData;

    /** @var array */
    private $createdFormData;

    /** @var array */
    private $fillAnswerData;

    /** @var array */
    private $answerData;

    /** @var array */
    private $digitalFormScheduleListData;

    /** @var array */
    private $fillScheduleData;

    /**
     * @When I want get digital all forms
     */
    public function iWantGetDigitalAllForms()
    {
        $this->get('/api/digital-form/form');
        $this->digitalFormListData = json_decode($this->getResponse()->getContent(), true);

        if (count($this->digitalFormListData) === 0) {
            throw new \Exception('At least one form must be present');
        }

        foreach ($this->digitalFormListData as $form) {
            if ($form['id'] < 1) {
                throw new \Exception('Invalid form id: ' . $form['id']);
            }
        }
    }

    /**
     * @When I want view first form from list
     */
    public function iWantViewFirstFormFromList()
    {
        $form = array_shift($this->digitalFormListData);

        $this->get('/api/digital-form/form/' . $form['id']);
        $this->singleFormData = json_decode($this->getResponse()->getContent(), true);

        if (empty($this->singleFormData['id'])) {
            throw new \Exception('Empty data was returned');
        }

        if (empty($this->singleFormData['steps'])) {
            throw new \Exception('Form must contains minimum one step');
        }
    }

    /**
     * @When I want fill digital form step fields: key :key, :field field with :value
     */
    public function iWantFillDigitalFormStepFields($key, $field, $value)
    {
        if (in_array($field, ['options', 'condition'])) {
            $value = json_decode($value, true);
        }

        if (isset($this->fillStepData[$key])) {
            $this->fillStepData[$key][$field] = $value;
        } else {
            $this->fillStepData[$key] = [$field => $value];
        }
    }

    /**
     * @When I want clear fill step data
     */
    public function iWantClearFillData()
    {
        $this->fillStepData = [];
    }

    /**
     * @When I want create digital form
     */
    public function iWantCreateDigitalForm()
    {
        $data = [
            'type' => DigitalForm::TYPE_INSPECTION,
            'title' => 'Form title',
            'steps' => $this->fillStepData,
            'days' => ['monday', 'thursday'],
            'timeFrom' => '12:12',
            'timeTo' => '16:16',
            'scopes' => $this->fillScheduleData,
        ];
        $data = array_merge($data, $this->fillData);

        $this->post(
            '/api/digital-form/form',
            $data,
            [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        $this->createdFormData = json_decode($this->getResponse()->getContent(), true);

        if (empty($this->createdFormData['id'])) {
            throw new \Exception('Invalid created form data: ' . print_r($this->createdFormData, true));
        }
    }


    /**
     * @When I want to create an arbitrary digital form
     */
    public function iWantCreateDigitalFormTest()
    {
        $this->post('/api/digital-form/form', $this->fillData, ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->createdFormData = $this->getResponseData();

        if (empty($this->createdFormData['id'])) {
            throw new \Exception('Invalid answer id data: ' . print_r($this->createdFormData, true));
        }
    }

    /**
     * @When I want edit digital form
     */
    public function iWantEditDigitalForm()
    {
        $data = [
            'type' => DigitalForm::TYPE_INSPECTION,
            'title' => 'Form title',
            'steps' => $this->fillStepData,
            'days' => ['monday', 'thursday'],
            'timeFrom' => '12:12',
            'timeTo' => '16:16',
            'scopes' => $this->fillScheduleData,
        ];
        $data = array_merge($data, $this->fillData);

        $this->post(
            '/api/digital-form/form/' . $this->createdFormData['id'],
            $data,
            [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        $editedFormData = json_decode($this->getResponse()->getContent(), true);

        if (empty($editedFormData['id'])) {
            throw new \Exception('Invalid edited form data: ' . print_r($editedFormData, true));
        }
        if ($editedFormData['id'] - 1 !==  $this->createdFormData['id']) {
            throw new \Exception('Invalid edited form id (created id = ' . $this->createdFormData['id'] . ' , edited id = ' . $editedFormData['id'] . ' )');
        }
    }

    /**
     * @When I want fill digital form answer fields: stepId :stepId value :value and duration :duration
     */
    public function iWantFillDigitalFormAnswerFields($stepId, $value, $duration)
    {
        $this->fillAnswerData[$stepId] = ['value' => $value, 'duration' => $duration];
    }

    /**
     * @When I want to create answer to first digital form
     */
    public function iWantCreateDigitalFormAnswer()
    {
        $this->post(
            '/api/digital-form/answer',
            [
                'formId' => 1,
                'data' => $this->fillAnswerData,
            ],
            [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        $this->answerData = json_decode($this->getResponse()->getContent(), true);
        if (empty($this->answerData['id'])) {
            throw new \Exception('Invalid answer id data: ' . print_r($this->answerData, true));
        }
    }

    /**
     * @When I want create digital form answer
     */
    public function iWantCreateDigitalFormAnswerTest()
    {
        $this->post('/api/digital-form/answer', $this->fillData, ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->answerData = $this->getResponseData();

        if (empty($this->answerData['id'])) {
            throw new \Exception('Invalid answer id data: ' . print_r($this->answerData, true));
        }
    }

    /**
     * @When I want get single digital form answer
     */
    public function iWantGetSingleDigitalFormAnswer()
    {
        $this->get('/api/digital-form/answer/' . $this->answerData['id']);

        $tmp = json_decode($this->getResponse()->getContent(), true);
        if ($tmp['id'] !== $this->answerData['id']) {
            throw new \Exception('Invalid answer data: ' . print_r($tmp, true));
        }
    }

    /**
     * @When I want get all digital form schedulers
     */
    public function iWantGetAllDigitalFormSchedulers()
    {
        $this->get('/api/digital-form/schedule');
        $this->digitalFormScheduleListData = json_decode($this->getResponse()->getContent(), true);

        if (empty($this->digitalFormScheduleListData['data']) || count($this->digitalFormScheduleListData['data']) === 0) {
            throw new \Exception('At least one schedule must be present: ' . print_r($this->digitalFormScheduleListData));
        }

        foreach ($this->digitalFormScheduleListData['data'] as $item) {
            if ($item['id'] < 1) {
                throw new \Exception('Invalid schedule id: ' . $item['id']);
            }
        }
    }

    /**
     * @When I want get first digital form scheduler from list
     */
    public function iWantFetFirstDigitalFormSchedulerFromList()
    {
        $item = array_shift($this->digitalFormScheduleListData['data']);

        $this->get('/api/digital-form/schedule/' . $item['id']);
        $this->singleScheduleData = json_decode($this->getResponse()->getContent(), true);

        if (empty($this->singleScheduleData['id'])) {
            throw new \Exception('Empty data was returned');
        }

        if (empty($this->singleScheduleData['recipients'])) {
            throw new \Exception('Form must contains minimum one recipient');
        }
    }

    /**
     * @When I want fill digital form schedule fields: scopeKey :scopeKey type :type and value :value
     */
    public function iWantFillDigitalFormScheduleFields($scopeKey, $type, $value)
    {
        $this->fillScheduleData[$scopeKey] = ['type' => $type, 'value' => [$value]];
    }
}
