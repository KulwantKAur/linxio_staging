<?php

namespace App\Service\FuelCard\Mapper\File\Xlsx;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class CaltexTransactionDetailXlsxFile extends BaseFileMapper
{
    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.caltexTSDetail.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.caltexTSDetail.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.caltexTSDetail.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.caltexTSDetail.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.caltexTSDetail.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.caltexTSDetail.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.caltexTSDetail.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.caltexTSDetail.refueledFuelType'),
        ];
    }
}
