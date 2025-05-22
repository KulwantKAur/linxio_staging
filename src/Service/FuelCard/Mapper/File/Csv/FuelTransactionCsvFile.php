<?php

declare(strict_types=1);

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class FuelTransactionCsvFile extends BaseFileMapper
{
    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.fuelTransaction.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.fuelTransaction.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.fuelTransaction.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.fuelTransaction.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.fuelTransaction.refueled'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.fuelTransaction.refueledFuelType'),
            self::FIELD_TOTAL => $this->translateField('import.fuelTransaction.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.fuelTransaction.petrolStation'),
        ];
    }
}
