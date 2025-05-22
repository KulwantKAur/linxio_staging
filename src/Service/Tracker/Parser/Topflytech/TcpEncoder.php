<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\EncoderInterface;
use App\Service\Tracker\Parser\Topflytech\Model\BasePacket;

class TcpEncoder implements EncoderInterface
{
    private const RESPONSE_FALSE = '00';
    private const RESPONSE_LENGTH = '000F';

    /**
     * @param bool $isAuthenticated
     * @param string|null $payload
     * @return string
     * @throws \Exception
     */
    public function encodeAuthentication(bool $isAuthenticated, ?string $payload = null): string
    {
        $result = self::formatResponse($payload);

        return ($isAuthenticated) ? $result : self::RESPONSE_FALSE;
    }

    /**
     * @param string $payload
     * @param Device|null $device
     * @return string
     * @throws \Exception
     */
    private static function formatResponse(string $payload, ?Device $device = null): string
    {
        // @todo verify that basePacket is ok before receiving last payload
        $basePacket = BasePacket::createFromTextPayload($payload);
        $payload = (new TcpDecoder())->getLastPayload($payload, $device);
        $baseResponse = substr($payload, 0, 30);

        switch ($basePacket->getMessageType()) {
            case Data::ALARM_MESSAGE_TYPE:
            case Data::ALARM_MESSAGE_TLD2_L_TYPE:
                switch ($basePacket->getProtocol()) {
                    case TcpDecoder::PROTOCOL_TLD1DADE:
                        $alarmType = substr($payload, 66, 2);
                        break;
                    case TcpDecoder::PROTOCOL_TLP1:
                        $alarmType = substr($payload, 32, 2);
                        break;
                    default:
                        $alarmType = substr($payload, 74, 2);
                        break;
                }

                $response = substr_replace($baseResponse, '0010', 6, 4) . $alarmType;
                break;
            case Data::ALARM_MESSAGE_TLW2_TYPE:
                // @todo check
                $alarmType = substr($payload, 90, 2);
                $response = substr_replace($baseResponse, '0010', 6, 4) . $alarmType;
                break;
            case Data::ACCIDENT_VIA_ACCELERATION_MESSAGE_TYPE:
                $accidentCode = substr($payload, 30, 2);
                $response = substr_replace($baseResponse, '0010', 6, 4) . $accidentCode;
                break;
            case Data::LOGIN_MESSAGE_TYPE:
            case Data::POSITION_MESSAGE_TYPE:
            case Data::HEARTBEAT_MESSAGE_TYPE:
            case Data::DRIVER_BEHAVIOR_GNSS_MESSAGE_TYPE:
            case Data::DRIVER_BEHAVIOR_ACCELERATION_MESSAGE_TYPE:
            case Data::RS232_MESSAGE_TYPE:
            case Data::DTC_VIN_MESSAGE_TYPE:
            case Data::BLE_MESSAGE_TYPE:
            case Data::NETWORK_INFORMATION_MESSAGE_TYPE:
            default:
                $response = substr_replace($baseResponse, self::RESPONSE_LENGTH, 6, 4);
                break;
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function encodeData($textPayload, ?Device $device = null): string
    {
        return self::formatResponse($textPayload, $device);
    }
}
