<?php

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class CireServicesCsvFile extends BaseFileMapper
{
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.transactionV4.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.transactionV4.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.transactionV4.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.transactionV4.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.transactionV4.refueled'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.transactionV4.refueledFuelType'),
            self::FIELD_TOTAL => $this->translateField('import.transactionV4.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.transactionV4.petrolStation'),
            self::FIELD_ODOMETER => $this->translateField('import.chevron.odometer'),
        ];
    }
}
