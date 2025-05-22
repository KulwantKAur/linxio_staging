<?php

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class TransactionNorthernBeachesCsvFile extends BaseFileMapper
{

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.transactionNorthernBeaches.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.transactionNorthernBeaches.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.transactionNorthernBeaches.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.transactionNorthernBeaches.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.transactionNorthernBeaches.refueled'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.transactionNorthernBeaches.refueledFuelType'),
            self::FIELD_TOTAL => $this->translateField('import.transactionNorthernBeaches.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.transactionNorthernBeaches.petrolStation'),
        ];
    }
}
