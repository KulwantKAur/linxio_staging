<?php

declare(strict_types=1);

namespace App\Service\FuelCard\Mapper\File\Xlsx;

use App\Service\FuelCard\Mapper\BaseFileMapper;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;

class TransactionV3XlsxFile extends BaseFileMapper
{
    protected bool $isShowTime = false;

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.transactionV3.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.transactionV3.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.transactionV3.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.transactionV3.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.transactionV3.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.transactionV3.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.transactionV3.refueledFuelType'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        $data = $this->checkTransactionDate($data, self::TRANSACTION_V3_DATE_FORMAT);

        return $this->processingNumericFields($data);
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
