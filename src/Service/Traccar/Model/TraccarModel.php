<?php

namespace App\Service\Traccar\Model;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Service\Traccar\Model\EventAttributes\TraccarEventAttributes;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributes;
use Carbon\Carbon;

abstract class TraccarModel
{
    public const KNOTS_TO_KM_H_FACTOR = 1.852;

    /**
     * @return string|null
     */
    abstract public function getProtocol(): ?string;

    /**
     * @return \stdClass|null
     */
    abstract public function getRawAttributes(): ?\stdClass;

    /**
     * @param Device $device
     * @param string $vendorName
     * @return bool
     */
    private static function isDeviceWithVendor(Device $device, string $vendorName)
    {
        return strrpos($device->getModelName(), $vendorName) !== false;
    }

    /**
     * @param TraccarModel $traccarModel
     * @param Device|null $device
     * @return TraccarPositionAttributes|null
     * @throws \Exception
     */
    public function handlePositionAttributes(TraccarModel $traccarModel, ?Device $device): ?TraccarPositionAttributes
    {
        $protocol = self::getProtocolByModelOrDevice($traccarModel, $device);

        if ($protocol) {
            return TraccarPositionAttributes::getInstance($protocol, $traccarModel->getRawAttributes(), $device);
        }

        return null;
    }

    /**
     * @param TraccarModel $traccarModel
     * @param Device|null $device
     * @return TraccarEventAttributes
     * @throws \Exception
     */
    public function handleEventAttributes(TraccarModel $traccarModel, ?Device $device): ?TraccarEventAttributes
    {
        $protocol = self::getProtocolByModelOrDevice($traccarModel, $device);

        if ($protocol) {
            return TraccarEventAttributes::getInstance($protocol, $traccarModel->getRawAttributes(), $device);
        }

        return null;
    }

    /**
     * @param string $deviceDate
     * @return \DateTimeInterface
     */
    public function convertDeviceDateToDatetime(string $deviceDate): \DateTimeInterface
    {
        $deviceDate = (ctype_digit($deviceDate) && strlen($deviceDate) == 13) ? ($deviceDate / 1000) : $deviceDate;

        return Carbon::parse($deviceDate);
    }

    /**
     * @param mixed $field
     * @return mixed
     */
    public function handlePossibleZeroValueInRawField($field)
    {
        return isset($field)
            ? (($field == 0) ? null : $field)
            : null;
    }

    /**
     * @param float $knots
     * @return float
     */
    public function convertKnotsToKmH(float $knots): float
    {
        return round($knots * self::KNOTS_TO_KM_H_FACTOR, 1);
    }

    /**
     * @param \stdClass|array $fields
     * @return \stdClass
     */
    public static function convertArrayToObject($fields): \stdClass
    {
        if (is_array($fields)) {
            if (empty($fields)) {
                return new \stdClass();
            }

            $fields = json_decode(json_encode($fields));
        }

        return $fields;
    }

    /**
     * @param \stdClass|array $fields
     * @return array
     */
    public static function convertObjectToArray($fields): array
    {
        if (is_object($fields)) {
            $fields = json_decode(json_encode($fields), true);
        }

        return $fields;
    }

    /**
     * @param TraccarModel $traccarModel
     * @param Device|null $device
     * @return string|null
     * @throws \Exception
     */
    public static function getProtocolByModelOrDevice(TraccarModel $traccarModel, ?Device $device): ?string
    {
        if ($traccarModel->getProtocol()) {
            return $traccarModel->getProtocol();
        }

        if ($device) {
            switch ($device->getVendorName()) {
                case DeviceVendor::VENDOR_TELTONIKA:
                    return TraccarData::PROTOCOL_TELTONIKA;
                case DeviceVendor::VENDOR_ULBOTECH:
                    return TraccarData::PROTOCOL_ULBOTECH;
                case DeviceVendor::VENDOR_TRACCAR:
                    switch (true) {
                        case self::isDeviceWithVendor($device, DeviceVendor::VENDOR_TELTONIKA):
                            return TraccarData::PROTOCOL_TELTONIKA;
                        case self::isDeviceWithVendor($device, DeviceVendor::VENDOR_ULBOTECH):
                            return TraccarData::PROTOCOL_ULBOTECH;
                        case self::isDeviceWithVendor($device, DeviceModel::TRACCAR_VENDOR_CONCOX):
                            return TraccarData::PROTOCOL_CONCOX;
                        case self::isDeviceWithVendor($device, DeviceModel::TRACCAR_VENDOR_MEITRACK):
                            return TraccarData::PROTOCOL_MEITRACK;
                        case self::isDeviceWithVendor($device, DeviceModel::TRACCAR_VENDOR_QUECLINK):
                            return TraccarData::PROTOCOL_QUECLINK;
                        case self::isDeviceWithVendor($device, DeviceModel::TRACCAR_VENDOR_DIGITAL_MATTER):
                            return TraccarData::PROTOCOL_DIGITAL_MATTER;
                        case self::isDeviceWithVendor($device, DeviceModel::TRACCAR_VENDOR_EELINK):
                            return TraccarData::PROTOCOL_EELINK;
                        default:
                            return null;
                    }
                default:
                    return null;
            }
        }

        return null;
    }
}