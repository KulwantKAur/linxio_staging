<?php

namespace App\Tests\Behat\Context\Traits;

use Behat\Gherkin\Node\TableNode;

trait TrackerTeltonikaTrait
{
    /**
     * @When I want to connect to teltonika tcp server
     */
    public function connectToTeltonikaTcpServer()
    {
        try {
            $fp = fsockopen(getenv('TCP_SERVER_HOST'), getenv('TCP_TELTONIKA_SERVER_PORT'), $errno, $errstr, 10);

            if (!$fp) {
                echo "$errstr ($errno)<br />\n";
                return false;
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        fclose($fp);
        return true;
    }

    /**
     * @When I want to connect to wrong tcp server
     */
    public function connectToWrongTcpServer()
    {
        try {
            $fp = fsockopen(getenv('TCP_SERVER_HOST'), 3085, $errno, $errstr, 10);

            if (!$fp) {
                echo "$errstr ($errno)<br />\n";
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }

        fclose($fp);
        return true;
    }

    /**
     * @When I want to send teltonika tcp data to api with socket :socket
     */
    public function sendTeltonikaTcpDataToApi($socket)
    {
        $this->post('/api/tracker/teltonika/tcp', $this->fillData, ['HTTP_X-SOCKET-ID' => $socket]);
    }

    /**
     * @Given There are following tracker payload from teltonika tracker with socket :socket:
     */
    public function thereAreaFollowingTeltonikaTrackerPayload($socket, TableNode $tableNode)
    {
        foreach ($tableNode as $row) {
            $this->post('/api/tracker/teltonika/tcp', ['payload' => $row['payload']], ['HTTP_X-SOCKET-ID' => $socket]);
        }
    }

    /**
     * @When I want create teltonika device coordinates history
     */
    public function createDeviceCoordinatesHistory()
    {
        $auth = ['payload' => '000F383632323539353838383334323930'];
        $params = http_build_query($auth);
        $this->post('/api/tracker/teltonika/tcp', $params, ['HTTP_X-SOCKET-ID' => 'test-socket-id']);

        $payload = ['payload' => '00000000000000FE080400000113fc208dff000f14f650209cca80006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cdResponse::HTTP_OK009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612'];
        $params = http_build_query($payload);
        $this->post('/api/tracker/teltonika/tcp', $params, ['HTTP_X-SOCKET-ID' => 'test-socket-id']);
    }
}
