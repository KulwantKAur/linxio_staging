<?php

namespace App\Service\FuelCard\Mapper\File\Xlsx;

use App\Service\FuelCard\Mapper\BaseFileMapper;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;

class NswXlsxFile extends BaseFileMapper
{

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.nsw.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.nsw.transactionTime'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.nsw.fuelCardNumber'),
            self::FIELD_VEHICLE => $this->translateField('import.nsw.vehicle'),
            self::FIELD_ODOMETER => $this->translateField('import.nsw.odometer'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.nsw.refueledFuelType'),
            self::FIELD_REFUELED => $this->translateField('import.nsw.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.nsw.total')
        ];
    }

    public function specialPrepareFields(array $data): array
    {
        $data = $this->checkTransactionDate($data, self::NSW_DATE_FORMAT);

        return $this->processingNumericFields($data);
    }

    public function checkSkipLine(array $data): bool
    {
        return (is_string($data[self::FIELD_TRANSACTION_DATE])
                && !is_numeric(strtotime($data[self::FIELD_TRANSACTION_DATE])))
            || empty($data[self::FIELD_REFUELED])
            || empty($data[self::FIELD_VEHICLE])
            ;
    }

    public function checkTransactionDate($data, $format = self::DEFAULT_DATE_FORMAT)
    {
        try {
            $data[self::FIELD_TRANSACTION_DATE] = is_numeric($data[self::FIELD_TRANSACTION_DATE])
                ? SharedDate::excelToDateTimeObject($data[self::FIELD_TRANSACTION_DATE])->format($format) : false;
        } catch (\Exception $e) {
            $data[self::FIELD_TRANSACTION_DATE] = false;
        }

        return $data;
    }
}
