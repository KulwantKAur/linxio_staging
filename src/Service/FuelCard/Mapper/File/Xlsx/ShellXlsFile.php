<?php

namespace App\Service\FuelCard\Mapper\File\Xlsx;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class ShellXlsFile extends BaseFileMapper
{

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.shell.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.shell.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.shell.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.shell.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.shell.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.shell.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.shell.refueledFuelType'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        return $this->processingNumericFields($data);
    }
}
