<?php

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class CaltexTransactionDetailCsvFile extends BaseFileMapper
{
    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.caltexTSDetailCsv.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.caltexTSDetailCsv.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.caltexTSDetailCsv.transactionDate'),
            self::FIELD_TRANSACTION_TIME => $this->translateField('import.caltexTSDetailCsv.transactionTime'),
            self::FIELD_REFUELED => $this->translateField('import.caltexTSDetailCsv.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.caltexTSDetailCsv.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.caltexTSDetailCsv.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.caltexTSDetailCsv.refueledFuelType'),
        ];
    }

    public function specialPrepareFields(array $data)
    {
        $data = $this->processingCardNumberFields($data);
        $data = $this->checkTransactionDate($data, self::CALTEX_DATE_FORMAT_V2);
        $data = $this->processingNumericFields($data);

        return $data;
    }

    public function checkTransactionDate($data, $format = self::DEFAULT_DATE_FORMAT)
    {
        try {
            $data[self::FIELD_TRANSACTION_DATE] = $this->parseDate($format, $data[self::FIELD_TRANSACTION_DATE]);
        } catch (\Exception) {
            try {
                $data[self::FIELD_TRANSACTION_DATE] =
                    $this->parseDate(self::DEFAULT_DATE_FORMAT, $data[self::FIELD_TRANSACTION_DATE]);
            } catch (\Exception) {
                $data[self::FIELD_TRANSACTION_DATE] = false;
            }
        }

        return $data;
    }
}
