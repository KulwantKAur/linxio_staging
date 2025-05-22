<?php

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class BpInvoiceCsvFile extends BaseFileMapper
{

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.bpInvoice.vehicle'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.bpInvoice.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.bpInvoice.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.bpInvoice.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.bpInvoice.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.bpInvoice.petrolStation'),
        ];
    }
}
