<?php

declare(strict_types=1);

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class ShellCsvFile extends BaseFileMapper
{
    protected bool $isSetDateTime = true;

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.shellCard.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.shellCard.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.shellCard.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.shellCard.refueled'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.shellCard.refueledFuelType'),
            self::FIELD_TOTAL => $this->translateField('import.shellCard.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.shellCard.petrolStation'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        $data = $this->checkTransactionDate($data, self::SHELL_CARD_DATE_FORMAT);

        return $this->processingNumericFields($data);
    }
}
