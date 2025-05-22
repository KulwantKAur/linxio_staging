<?php

namespace App\Service\FuelCard\Mapper\File\Xlsx;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class TransactionV2XlsFile extends BaseFileMapper
{

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.transactionV2.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.transactionV2.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.transactionV2.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.transactionV2.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.transactionV2.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.transactionV2.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.transactionV2.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.transactionV2.refueledFuelType'),
        ];
    }
}
