<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser;

use App\Service\Tracker\Interfaces\LocationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class OpenCellIdHelper
 * @link https://opencellid.org
 *
 * @example {"lon":"151.169136","lat":"-33.880791","range":"1000"}
 */
class OpenCellIdHelper
{
    private const URL = 'https://opencellid.org/ajax/searchCell.php';

    private $location;

    private $mcc;
    private $mnc;
    private $lac;
    private $cellId;

    private $lng;
    private $lat;
    private $range;

    /**
     * OpenCellId constructor.
     * @param $mcc
     * @param $mnc
     * @param $lac
     * @param $cellId
     */
    public function __construct($mcc, $mnc, $lac, $cellId)
    {
        $this->mcc = $mcc;
        $this->mnc = $mnc;
        $this->lac = $lac;
        $this->cellId = $cellId;
    }

    /**
     * @return \stdClass|null
     */
    public function fetchCoordinates(): ?\stdClass
    {
        $client = new Client();
        $data = null;

        try {
            $result = $client->request('GET', self::URL, [
                'query' => [
                    'mcc' => $this->mcc,
                    'mnc' => $this->mnc,
                    'lac' => $this->lac,
                    'cell_id' => $this->cellId,
                ],
                'timeout' => 5,
            ]);

            $dataJson = $result->getBody()->getContents();

            if ($dataJson) {
                $data = json_decode($dataJson);
            }
        } catch (GuzzleException $e) {
            return $data;
        }

        return ($data instanceof \stdClass) ? $data : null;
    }

    /**
     * @param LocationInterface $location
     * @return static
     */
    public static function createFromLocation(LocationInterface $location): self
    {
        $mcc = $location->getMobileCountryCode();
        $mnc = $location->getMobileNetworkCode();
        $lac = $location->getLocationAreaCode();
        $cellId = $location->getStationId();

        return new self($mcc, $mnc, $lac, $cellId);
    }
}
