<?php

namespace App\Service\FuelCard\Mapper\File\Xlsx;

use App\Service\FuelCard\Mapper\BaseFileMapper;
use Carbon\Carbon;

class CaltexXlsxFile extends BaseFileMapper
{
    protected bool $isShowTime = false;
    protected int $headingSearchDepth = 70;

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.caltex.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.caltex.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.caltex.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.caltex.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.caltex.refueledFuelType'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        $searchCardNoPosition1 = strpos(
            $data[self::FIELD_PETROL_STATION],
            $this->translateField('import.caltex.fuelCardNumber')
        );
        $searchCardNoPosition2 = strpos(
            $data[self::FIELD_TRANSACTION_DATE],
            $this->translateField('import.caltex.fuelCardNumber')
        );
        $searchEndPosition = strpos($data[self::FIELD_REFUELED_FUEL_TYPE], 'Total');

        if (($searchCardNoPosition1 !== false) || ($searchCardNoPosition2 !== false)) {
            $searchData = ($searchCardNoPosition1 !== false)
                ? $data[self::FIELD_PETROL_STATION]
                : $data[self::FIELD_TRANSACTION_DATE];

            $parsingStr = explode(' ', $searchData);
            $this->temporaryData[self::FIELD_FUEL_CARD_NUMBER]
                = $parsingStr[2]. $parsingStr[3]. $parsingStr[4]. $parsingStr[5];
            $this->temporaryData[self::FIELD_VEHICLE] =  $parsingStr[7];
        }
        if (!empty($this->temporaryData)) {
            foreach ($this->temporaryData as $dataK => $dataV) {
                $data[$dataK] = $dataV;
            }
        }
        if ($searchEndPosition !== false) {
            unset($this->temporaryData);
        }

        $data = $this->checkTransactionDate($data, self::CALTEX_DATE_FORMAT);
        $data = $this->processingNumericFields($data);

        return $data;
    }
}
