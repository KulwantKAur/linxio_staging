<?php

namespace App\Service\FuelCard\Mapper\File\Txt;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class MotorpassTxtFile extends BaseFileMapper
{
    protected bool $isShowTime = false;
    protected int $headingSearchDepth = 70;

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.motorpass.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.motorpass.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.motorpass.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.motorpass.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.motorpass.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.motorpass.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.motorpass.refueledFuelType'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        $data = $this->checkTransactionDate($data);
        $data = $this->processingNumericFields($data);

        $searchEndPosition = strpos($data[self::FIELD_FUEL_CARD_NUMBER], 'CARD');

        if (!empty($data[self::FIELD_VEHICLE]) || !empty($data[self::FIELD_FUEL_CARD_NUMBER])) {
            $parsingStr = explode(' ', $data[self::FIELD_VEHICLE]);

            $this->temporaryData[self::FIELD_FUEL_CARD_NUMBER] = $data[self::FIELD_FUEL_CARD_NUMBER];
            $this->temporaryData[self::FIELD_VEHICLE] = $parsingStr[0];
        }

        if (!empty($this->temporaryData)) {
            foreach ($this->temporaryData as $dataK => $dataV) {
                $data[$dataK] = $dataV;
            }
        }
        if ($searchEndPosition !== false) {
            unset($this->temporaryData);
        }

        return $data;
    }
}
