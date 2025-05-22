<?php

namespace App\Tests\Behat\Support;

trait ResponseTrait
{
    /**
     * @Then I see field :field filled with :value
     */
    public function iSeeFieldFilledWith($field, $value)
    {
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'null') {
            $value = null;
        }

        if (!is_numeric($value)) {
            $value = json_encode($value);
        }

        if (strpos($value, 'string(') !== false) {
            $value = preg_replace('/\D/', '', $value) . '';
            $value = json_encode($value);
        }

        if (strpos($value, 'custom(') !== false) {
            $value = str_replace('custom(', '', $value);
            $value = str_replace(')', '', $value);
        }

        try {
            $this->jsonResponse()
                ->equal($value, ['at' => "$field"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Then I see field :field filled with saved :savedField
     */
    public function iSeeFieldFilledWithSavedField($field, $savedField)
    {
        $value = $this->$savedField;
        $this->iSeeFieldFilledWith($field, $value);
    }

    /**
     * @Then I see field :field
     */
    public function iSeeField($field)
    {
        try {
            $this->jsonResponse()
                ->hasPath($field);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Then I see field :field is not null
     */
    public function iSeeFieldIsNotNull($field)
    {
        try {
            $this->jsonResponse()
                ->notEqual(json_encode(null), ['at' => $field]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Then I see csv item number :number field :field filled with :value
     */
    public function iSeeCsvFieldFilledWith($number, $field, $value)
    {
        try {
            $data = $this->serializer->decode($this->getResponseContent(), 'csv');
            if (!array_key_exists(0, $data)) {
                $item = $data;
            } else {
                $item = $data[$number];
            }
            if (!array_key_exists($field, $item)) {
                throw new \Exception('Field don\'t exist');
            }
            if ($item[$field] !== $value) {
                throw new \Exception('Fields is not equal: ' . $item[$field] . '!==' . $value);
            }

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Then I see csv item number :number field :field is not empty
     */
    public function iSeeCsvFieldIsNotEmpty($number, $field)
    {
        try {
            $data = $this->serializer->decode($this->getResponseContent(), 'csv');
            if (!array_key_exists(0, $data)) {
                $item = $data;
            } else {
                $item = $data[$number];
            }
            if (!array_key_exists($field, $item)) {
                throw new \Exception('Field don\'t exist');
            }
            if (!($item[$field] ?? null)) {
                throw new \Exception('Fields is not exist');
            }

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }
}
