<?php

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class MotorpassV2File extends BaseFileMapper
{
    protected bool $isShowTime = false;

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.motorpassV2.vehicle'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.motorpassV2.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.motorpassV2.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.motorpassV2.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.motorpassV2.petrolStation'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        $searchEndPosition = strripos(
            $data[self::FIELD_TRANSACTION_DATE],
            $this->translateField('import.motorpassV2.searchEndPosition')
        );

        $data = $this->checkTransactionDate($data, self::MOTORPASS_V2_DATE_FORMAT);
        $data = $this->processingNumericFields($data);

        if (!empty($data[self::FIELD_VEHICLE])) {
            $parsingStr = explode(' ', $data[self::FIELD_VEHICLE]);
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
