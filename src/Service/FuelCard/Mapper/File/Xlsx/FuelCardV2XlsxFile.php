<?php

namespace App\Service\FuelCard\Mapper\File\Xlsx;

use App\Service\FuelCard\Mapper\BaseFileMapper;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FuelCardV2XlsxFile extends BaseFileMapper
{
    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.fuelCardV2.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.fuelCardV2.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.fuelCardV2.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.fuelCardV2.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.fuelCardV2.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.fuelCardV2.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.fuelCardV2.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.fuelCardV2.refueledFuelType'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        if (!empty($data[self::FIELD_TRANSACTION_DATE]) && is_integer($data[self::FIELD_TRANSACTION_DATE])) {
            $data[self::FIELD_TRANSACTION_DATE] = Date::excelToDateTimeObject($data[self::FIELD_TRANSACTION_DATE])
                ->format(self::FUEL_CARD_V2_DATE_FORMAT);
        }

        if (!empty($data[self::FIELD_TRANSACTION_TIME])
            && is_float($data[self::FIELD_TRANSACTION_TIME])
        ) {
            $data[self::FIELD_TRANSACTION_TIME] = Date::excelToDateTimeObject($data[self::FIELD_TRANSACTION_TIME])
                ->format(self::FUEL_CARD_V2_TIME_FORMAT);
        } else {
            $data[self::FIELD_TRANSACTION_TIME] = null;
        }

        $data = $this->checkTransactionDate($data, self::FUEL_CARD_V2_DATE_FORMAT);

        return $this->processingNumericFields($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function checkSkipLine(array $data): bool
    {
        return (!is_numeric(strtotime($data[self::FIELD_TRANSACTION_DATE])))
            || empty($data[self::FIELD_REFUELED])
            || empty($data[self::FIELD_VEHICLE])
            || empty($data[self::FIELD_TRANSACTION_TIME])
            ;
    }
}
