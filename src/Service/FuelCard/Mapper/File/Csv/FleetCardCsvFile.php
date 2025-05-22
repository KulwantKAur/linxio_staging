<?php

namespace App\Service\FuelCard\Mapper\File\Csv;

use App\Service\FuelCard\Mapper\BaseFileMapper;

class FleetCardCsvFile extends BaseFileMapper
{
    protected bool $isSearchHeader = false;
    protected bool $isShowTime = false;

    public const FIELD_INDEX = 'index';
    public const FIELD_START_POSITION = 'startPosition';
    public const FIELD_REFUELING_DATA = 'refuelingData';

    protected array $header = [
        0 => self::FIELD_INDEX,
        2 => self::FIELD_VEHICLE,
        5 => self::FIELD_TRANSACTION_DATE,
        8 => self::FIELD_PETROL_STATION,
        11 => self::FIELD_REFUELED_FUEL_TYPE,
        12 => self::FIELD_FUEL_CARD_NUMBER,
        13 => self::FIELD_START_POSITION,
        15 => self::FIELD_REFUELED,
        18 => self::FIELD_TOTAL,
    ];

    protected array $refuelingDataIndex = [
        self::FIELD_FUEL_CARD_NUMBER => 3,
        self::FIELD_REFUELING_DATA => 4,
    ];

    /**
     * @return array
     */
    public function getInternalMappedFields(): array
    {
        return [
            self::FIELD_VEHICLE => $this->translateField('import.fleetCard.vehicle'),
            self::FIELD_FUEL_CARD_NUMBER => $this->translateField('import.fleetCard.fuelCardNumber'),
            self::FIELD_TRANSACTION_DATE => $this->translateField('import.fleetCard.transactionDate'),
            self::FIELD_REFUELED => $this->translateField('import.fleetCard.refueled'),
            self::FIELD_TOTAL => $this->translateField('import.fleetCard.total'),
            self::FIELD_PETROL_STATION => $this->translateField('import.fleetCard.petrolStation'),
            self::FIELD_REFUELED_FUEL_TYPE => $this->translateField('import.fleetCard.refueledFuelType'),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function specialPrepareFields(array $data): array
    {
        $data = $this->checkTransactionDate($data);
        $data = $this->processingNumericFields($data);

        $searchStartPosition = strpos(
            $data[self::FIELD_START_POSITION],
            'Fleetcard All Srvces (Orange)'
        );
        if ($searchStartPosition !== false) {
            $this->temporaryData[self::FIELD_FUEL_CARD_NUMBER] = (int) $data[self::FIELD_FUEL_CARD_NUMBER];
            $this->temporaryData[self::FIELD_INDEX] = (int) $data[self::FIELD_INDEX];
        }
        if (!empty($this->temporaryData)) {
            foreach ($this->temporaryData as $dataK => $dataV) {
                $data[$dataK] = $dataV;
            }
        }

        if ($data[self::FIELD_INDEX] === $this->refuelingDataIndex[self::FIELD_FUEL_CARD_NUMBER]
            && (!empty($this->temporaryData[self::FIELD_INDEX])
                && ($this->temporaryData[self::FIELD_INDEX] !== $data[self::FIELD_INDEX])) ? true : false
            && (!is_numeric(strtotime($data[self::FIELD_TRANSACTION_DATE])))
        ) {
            unset($this->temporaryData);
        }
        return $data;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function checkSkipLine(array $data): bool
    {
        $skipPosition = strpos(
            $data[self::FIELD_REFUELED_FUEL_TYPE],
            'Periodic Fee/Stamp Duty'
        );

        $skipLine = ((!is_numeric(strtotime($data[self::FIELD_TRANSACTION_DATE])))
            || empty($data[self::FIELD_REFUELED])
            || empty($data[self::FIELD_VEHICLE])
            || ($skipPosition !== false)
            || !in_array($data[self::FIELD_INDEX], array_values($this->refuelingDataIndex), true)
            )
            ? true
            : false;

        return $skipLine;
    }
}
