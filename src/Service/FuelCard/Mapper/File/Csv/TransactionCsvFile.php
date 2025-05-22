<?php

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class TransactionCsvFile extends BaseFileMapper
{

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.transaction.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.transaction.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.transaction.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.transaction.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.transaction.refueled'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.transaction.refueledFuelType'),
            self::FIELD_TOTAL => $this->translateField('import.transaction.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.transaction.petrolStation'),
        ];
    }
}
