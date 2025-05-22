<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAccidentViaAcc;
use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAlarm;
use App\Service\Tracker\Parser\Topflytech\Model\BaseLogin;
use App\Service\Tracker\Parser\Topflytech\Model\BasePacket;
use App\Service\Tracker\Parser\Topflytech\Model\BasePosition;
use App\Service\Tracker\Parser\Topflytech\Model\DriverBehaviorBase;
use App\Service\Tracker\Parser\Topflytech\Model\HeartBeat;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\Position AS PositionTLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\BLE AS BLETLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\BLE AS BLETLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\Model\TLP1\BLE AS BLETLP1;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\BLE AS BLETLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\AccidentViaAcc AS AccidentViaAccTLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\AccidentViaAcc as AccidentViaAccTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\AccidentViaAcc as AccidentViaAccTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\Alarm AS AlarmTLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\Alarm as AlarmTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLP1\Alarm as AlarmTLP1;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\Alarm as AlarmTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLD2L\Alarm as AlarmTLD2L;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\DriverBehaviorAM AS DriverBehaviorAMTLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\DriverBehaviorAM AS DriverBehaviorAMTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\DriverBehaviorAM AS DriverBehaviorAMTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\Position as PositionTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\Model\TLP1\Position as PositionTLP1;
use App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\Position as PositionTLW212BL;
use App\Service\Tracker\Parser\Topflytech\Model\TLD2L\Position as PositionTLD2L;

class TcpDecoder implements DecoderInterface
{
    public const PROTOCOL_TLW1 = '2525';
    public const PROTOCOL_TLD1AE = self::PROTOCOL_TLW1;
    public const PROTOCOL_TLD1DADE = '2626';
    public const PROTOCOL_TLP1 = '2727';

    private function mayMessageHaveMultipleData(string $messageType): bool
    {
        switch ($messageType) {
            case Data::POSITION_MESSAGE_TYPE:
            case Data::POSITION_MESSAGE_TLW2_TYPE:
            case Data::POSITION_MESSAGE_TLD2_L_TYPE:
                return true;
            default:
                return false;
        }
    }

    /**
     * @param string $payload
     * @param Device|null $device
     * @param array $resultData
     * @return array
     * @throws \Exception
     */
    private function updateDataWithMultipleMessages(string $payload, ?Device $device, array $resultData): array
    {
        $basePacket = self::getBasePacket($payload);

        if ($this->mayMessageHaveMultipleData($basePacket->getMessageType())) {
            $messageLength = Data::getMessageLengthByTypeAndModel($basePacket->getMessageType(), $device->getModel());

            if ($messageLength && strlen($payload) > ($messageLength + BasePacket::PACKET_LENGTH)) {
                $totalLength = $messageLength + BasePacket::PACKET_LENGTH;
                $multiplePacketString = $basePacket->getProtocol() . $basePacket->getMessageType();
                $offset = $totalLength;

                while (strpos($payload, $multiplePacketString, $offset)) {
                    $payloadItem = substr($payload, $offset, $totalLength);

                    if (strlen($payloadItem) < ($messageLength + BasePacket::PACKET_LENGTH)) {
                        break;
                    }

                    $resultData[] = (new Data())->createFromTextPayload($payloadItem, $device->getModel());
                    $offset += $totalLength;
                }
            }
        }

        return $resultData;
    }

    /**
     * @param string $payload
     * @param Device|null $device
     * @return string
     * @throws \Exception
     */
    public function getLastPayload(string $payload, ?Device $device = null): string
    {
        $basePacket = self::getBasePacket($payload);
        $lastPayload = $payload;

        if ($this->mayMessageHaveMultipleData($basePacket->getMessageType()) && $device) {
            $messageLength = Data::getMessageLengthByTypeAndModel($basePacket->getMessageType(), $device->getModel());

            if ($messageLength && strlen($payload) > ($messageLength + BasePacket::PACKET_LENGTH)) {
                $totalLength = $messageLength + BasePacket::PACKET_LENGTH;
                $multiplePacketString = $basePacket->getProtocol() . $basePacket->getMessageType();
                $offset = $totalLength;

                while (strpos($payload, $multiplePacketString, $offset)) {
                    $payloadItem = substr($payload, $offset, $totalLength);

                    if (strlen($payloadItem) < ($messageLength + BasePacket::PACKET_LENGTH)) {
                        break;
                    }

                    $validPayloadItem = $payloadItem;
                    $offset += $totalLength;
                }

                $lastPayload = $validPayloadItem ?? $lastPayload;
            }
        }

        return $lastPayload;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function isAuthentication(string $data): bool
    {
        $messageType = substr($data, 4, 2);

        return ($messageType == Data::LOGIN_MESSAGE_TYPE) || ($messageType == Data::HEARTBEAT_MESSAGE_TYPE);
    }

    /**
     * @param string $data
     * @return bool
     */
    public function isLoginMessage(string $data): bool
    {
        $messageType = substr($data, 4, 2);

        return $messageType == Data::LOGIN_MESSAGE_TYPE;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function isCorrectTextPayload(string $data): bool
    {
        // @todo
        return true;
    }

    /**
     * @param string $payload
     * @return BaseLogin
     * @throws \Exception
     */
    public function decodeAuthentication(string $payload): ImeiInterface
    {
        $messageType = substr($payload, 4, 2);

        return ($messageType == Data::LOGIN_MESSAGE_TYPE) 
            ? BaseLogin::createFromTextPayload($payload)
            : HeartBeat::createFromTextPayload($payload);
    }

    /**
     * @param string $payload
     * @return BaseLogin
     * @throws \Exception
     */
    public function decodeLoginMessage(string $payload): BaseLogin
    {
        return BaseLogin::createFromTextPayload($payload);
    }

    /**
     * @param string $payload
     * @return string|string[]
     * @throws \Exception
     */
    public function getImei(string $payload): string
    {
        $basePacket = self::getBasePacket($payload);

        return $basePacket->getImei();
    }

    /**
     * @param string $payload
     * @return string|string[]
     * @throws \Exception
     */
    public function getProtocol(string $payload): string
    {
        $basePacket = self::getBasePacket($payload);

        return $basePacket->getProtocol();
    }

    /**
     * @param string $payload
     * @return string|string[]
     * @throws \Exception
     */
    public function getRequestMessageType(string $payload): string
    {
        $basePacket = self::getBasePacket($payload);

        return $basePacket->getMessageType();
    }

    public function isRequestTypeWithCorrectData(string $payload): bool
    {
        $requestMessageType = self::getRequestMessageType($payload);
        $payloadLength = strlen($payload);

        switch ($requestMessageType) {
            case Data::POSITION_MESSAGE_TYPE:
            case Data::POSITION_MESSAGE_TLW2_TYPE:
            case Data::POSITION_MESSAGE_TLD2_L_TYPE:
                return $payloadLength > BasePosition::PACKET_MINIMAL_LENGTH;
            case Data::ALARM_MESSAGE_TYPE:
            case Data::ALARM_MESSAGE_TLW2_TYPE:
            case Data::ALARM_MESSAGE_TLD2_L_TYPE:
                return $payloadLength > BaseAlarm::PACKET_MINIMAL_LENGTH;
            case Data::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
                $protocol = (new TcpDecoder())->getProtocol($payload);

                return $protocol == TcpDecoder::PROTOCOL_TLD1DADE || $protocol == TcpDecoder::PROTOCOL_TLW1;
            case Data::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                return $payloadLength > DriverBehaviorBase::PACKET_MINIMAL_LENGTH;
            case Data::BLE_MESSAGE_TYPE:
                if ($payloadLength < BaseBLE::PACKET_MINIMAL_LENGTH) {
                    return false;
                }

                $BLEDataCode = self::getBLEDataCode($payload);

                return BaseBLE::isRequestTypeWithData($BLEDataCode);
            case Data::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                return $payloadLength > BaseAccidentViaAcc::PACKET_LENGTH_WITHOUT_DATA;
            default:
                return false;
        }
    }

    public function isRequestTypeWithExtraData(string $payload): bool
    {
        $requestMessageType = self::getRequestMessageType($payload);
        $protocol = (new TcpDecoder())->getProtocol($payload);

        switch ($requestMessageType) {
            case Data::BLE_MESSAGE_TYPE:
                if (strlen($payload) < BaseBLE::PACKET_MINIMAL_LENGTH) {
                    return false;
                }
                
                $BLEDataCode = self::getBLEDataCode($payload);

                return BaseBLE::isRequestTypeWithExtraData($BLEDataCode);
            case Data::DTC_VIN_MESSAGE_TYPE:
                return $protocol == TcpDecoder::PROTOCOL_TLD1DADE;
            case Data::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                return true;
            case Data::NETWORK_INFORMATION_MESSAGE_TYPE:
                return $protocol == TcpDecoder::PROTOCOL_TLD1DADE || $protocol == TcpDecoder::PROTOCOL_TLW1;
            case Data::NETWORK_INFORMATION_2727_MESSAGE_TYPE:
                return $protocol == TcpDecoder::PROTOCOL_TLP1;
            case Data::ONEWIRE_MESSAGE_TYPE:
                return $protocol == TcpDecoder::PROTOCOL_TLW1;
            default:
                return false;
        }
    }

    /**
     * @param string $payload
     * @param Device|null $device
     * @return array
     * @throws \Exception
     */
    public function decodeData(string $payload, ?Device $device = null): array
    {
        if (!$this->isCorrectTextPayload($payload)) {
            throw new \Exception('Data is not correct');
        }

        $resultData = [
            (new Data())->createFromTextPayload($payload, $device->getModel())
        ];

        return $this->updateDataWithMultipleMessages($payload, $device, $resultData);
    }

    /**
     * @param array $data
     * @return array
     */
    public function orderByDateTime(array $data): array
    {
        usort($data, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a->getDateTime() < $b->getDateTime()) ? -1 : 1;
        });

        return $data;
    }

    /**
     * @param string $payload
     * @return bool
     * @throws \Exception
     */
    public function isCommandRequest(string $payload): bool
    {
        $requestMessageType = self::getRequestMessageType($payload);

        switch ($requestMessageType) {
            case Data::SETTING_MESSAGE_TYPE:
                return true;
            default:
                return false;
        }
    }

    /**
     * @param string $textPayload
     * @return string
     */
    public static function getBLEDataCode(string $textPayload): string
    {
        return substr($textPayload, 44, 4);
    }

    /**
     * @param string $modelName
     * @return string
     * @throws \Exception
     */
    public static function getProtocolByModelName(string $modelName): string
    {
        switch ($modelName) {
            case DeviceModel::TOPFLYTECH_TLD1_A_E:
            case DeviceModel::TOPFLYTECH_TLW1:
            case DeviceModel::TOPFLYTECH_TLW2_12BL:
            case DeviceModel::TOPFLYTECH_TLD2_L:
            case DeviceModel::TOPFLYTECH_TLW1_4:
            case DeviceModel::TOPFLYTECH_TLW1_8:
            case DeviceModel::TOPFLYTECH_TLW1_10:
            case DeviceModel::TOPFLYTECH_TLW2_2BL:
            case DeviceModel::TOPFLYTECH_TLW2_12B:
            case DeviceModel::TOPFLYTECH_TLD1:
            case DeviceModel::TOPFLYTECH_TLD1_D:
            case DeviceModel::TOPFLYTECH_PIONEERX_100:
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                return self::PROTOCOL_TLW1;
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
            case DeviceModel::TOPFLYTECH_TLD2_D:
                return self::PROTOCOL_TLD1DADE;
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
            case DeviceModel::TOPFLYTECH_TLP1_LM:
            case DeviceModel::TOPFLYTECH_TLP1_P:
            case DeviceModel::TOPFLYTECH_TLP1_SM:
            case DeviceModel::TOPFLYTECH_TLP2_SFB:
                return self::PROTOCOL_TLP1;
            default:
                throw new \Exception('Unsupported model name: ' . $modelName);
        }
    }

    /**
     * @param string $payload
     * @param string $modelName
     * @param \DateTimeInterface $createdAt
     * @return array
     * @throws \Exception
     */
    public function encodePayloadWithNewDateTime(
        string $payload,
        string $modelName,
        \DateTimeInterface $createdAt
    ): array {
        $emptyResponse = [
            'payload' => null,
            'createdAt' => null,
        ];

        if ($this->isRequestTypeWithCorrectData($payload) || $this->isRequestTypeWithExtraData($payload)) {
            $dateTimeModel = $this->getDateTimeModel($payload, $modelName);

            if (!$dateTimeModel) {
                return $emptyResponse;
            }

            $dtPayload = $dateTimeModel->getDateTimePayload($payload);
            $dt = Data::formatDateTime($dtPayload);
            $dtNeeded = (new \DateTime())->getTimestamp() - ($createdAt->getTimestamp() - $dt->getTimestamp());
            $dt->setTimestamp($dtNeeded);
            $dtString = Data::encodeDateTime($dt);
            $payload = $dateTimeModel->getPayloadWithNewDateTime($payload, $dtString);

            return [
                'payload' => $payload,
                'createdAt' => $dt,
            ];
        }

        return $emptyResponse;
    }

    /**
     * @param string $payload
     * @param string $modelName
     * @return DateTimePartPayloadInterface|null
     * @throws \Exception
     */
    public function getDateTimeModel(string $payload, string $modelName): ?DateTimePartPayloadInterface
    {
        $messageType = $this->getRequestMessageType($payload);

        switch ($modelName) {
            case DeviceModel::TOPFLYTECH_TLD1_A_E:
            case DeviceModel::TOPFLYTECH_TLW1:
            case DeviceModel::TOPFLYTECH_TLD2_L:
            case DeviceModel::TOPFLYTECH_TLW1_4:
            case DeviceModel::TOPFLYTECH_TLW1_8:
            case DeviceModel::TOPFLYTECH_TLW1_10:
            case DeviceModel::TOPFLYTECH_TLD1:
            case DeviceModel::TOPFLYTECH_TLD1_D:
            case DeviceModel::TOPFLYTECH_TLD2_D:
                switch ($messageType) {
                    case Data::POSITION_MESSAGE_TYPE:
                        return new PositionTLW1AndTLD1AE([]);
                    case Data::ALARM_MESSAGE_TYPE:
                        return new AlarmTLW1AndTLD1AE([]);
                    case Data::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
                    case Data::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                        return new DriverBehaviorAMTLW1AndTLD1AE([]);
                    case Data::BLE_MESSAGE_TYPE:
                        return new BLETLW1AndTLD1AE([]);
                    case Data::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                        return new AccidentViaAccTLW1AndTLD1AE([]);
                    case Data::POSITION_MESSAGE_TLD2_L_TYPE:
                        return new PositionTLD2L([]);
                    case Data::ALARM_MESSAGE_TLD2_L_TYPE:
                        return new AlarmTLD2L([]);
                    default:
                        return null;
                }
            case DeviceModel::TOPFLYTECH_TLW2_12BL:
            case DeviceModel::TOPFLYTECH_TLW2_2BL:
            case DeviceModel::TOPFLYTECH_TLW2_12B:
            case DeviceModel::TOPFLYTECH_PIONEERX_100:
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                switch ($messageType) {
                    case Data::POSITION_MESSAGE_TLW2_TYPE:
                        return new PositionTLW212BL([]);
                    case Data::ALARM_MESSAGE_TLW2_TYPE:
                        return new AlarmTLW212BL([]);
                    case Data::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
                    case Data::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                        return new DriverBehaviorAMTLW212BL([]);
                    case Data::BLE_MESSAGE_TYPE:
                        return new BLETLW212BL([]);
                    case Data::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                        return new AccidentViaAccTLW212BL([]);
                    default:
                        return null;
                }
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
                switch ($messageType) {
                    case Data::POSITION_MESSAGE_TYPE:
                        return new PositionTLD1DADE([]);
                    case Data::ALARM_MESSAGE_TYPE:
                        return new AlarmTLD1DADE([]);
                    case Data::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
                    case Data::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
                        return new DriverBehaviorAMTLD1DADE([]);
                    case Data::BLE_MESSAGE_TYPE:
                        return new BLETLD1DADE([]);
                    case Data::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                        return new AccidentViaAccTLD1DADE([]);
                    default:
                        return null;
                }
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
            case DeviceModel::TOPFLYTECH_TLP1_LM:
            case DeviceModel::TOPFLYTECH_TLP1_P:
            case DeviceModel::TOPFLYTECH_TLP1_SM:
            case DeviceModel::TOPFLYTECH_TLP2_SFB:
                switch ($messageType) {
                    case Data::POSITION_MESSAGE_TYPE:
                        return new PositionTLP1([]);
                    case Data::ALARM_MESSAGE_TYPE:
                        return new AlarmTLP1([]);
                    case Data::BLE_MESSAGE_TYPE:
                        return new BLETLP1([]);
                    default:
                        return null;
                }
            default:
                throw new \Exception('Unsupported model name: ' . $modelName);
        }
    }

    /**
     * @param string $payload
     * @return BasePacket
     * @throws \Exception
     */
    public static function getBasePacket(string $payload): BasePacket
    {
        return BasePacket::createFromTextPayload($payload);
    }
}
