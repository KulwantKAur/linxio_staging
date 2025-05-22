<?php

declare(strict_types=1);

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class ShellV2CsvFile extends BaseFileMapper
{
    protected bool $isSetDateTime = true;

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.shellV2.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.shellV2.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.shellV2.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.shellV2.refueled'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.shellV2.refueledFuelType'),
            self::FIELD_TOTAL => $this->translateField('import.shellV2.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.shellV2.petrolStation'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        $data = $this->checkTransactionDate($data, self::SHELL_V2_DATE_FORMAT);

        return $this->processingNumericFields($data);
    }
}
