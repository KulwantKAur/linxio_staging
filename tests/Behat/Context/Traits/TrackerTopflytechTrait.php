<?php

namespace App\Tests\Behat\Context\Traits;

use Behat\Gherkin\Node\TableNode;
use Carbon\Carbon;
use Carbon\Traits\Creator;

trait TrackerTopflytechTrait
{
    /**
     * @When I want to connect to topflytech tcp server
     */
    public function connectToTopflytechTcpServer()
    {
        try {
            $fp = fsockopen(getenv('TCP_SERVER_HOST'), getenv('TCP_TOPFLYTECH_SERVER_PORT'), $errno, $errstr, 10);

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
     * @Given There are following tracker payload from topflytech tracker with socket :socket:
     */
    public function thereAreaFollowingTopflytechTrackerPayload($socket, TableNode $tableNode)
    {
        foreach ($tableNode as $row) {
            $this->post('/api/tracker/topflytech/tcp', ['payload' => $row['payload']], ['HTTP_X-SOCKET-ID' => $socket]);
        }
    }

    /**
     * @Given There are following tracker payload from topflytech tracker, replace date now :isDateReplace, offset :offset, length :length, with socket :socket:
     */
    public function thereAreaFollowingTopflytechTrackerPayloadWithReplaceDate(
        $socket,
        bool $isDateReplace,
        int $offset,
        int $length,
        TableNode $tableNode
    ) {
        foreach ($tableNode as $row) {
            if ($isDateReplace) {
                $date = (new Carbon())->format('ymd');
                $row['payload'] = substr_replace($row['payload'], $date, $offset, $length);
            }

            $this->post('/api/tracker/topflytech/tcp', ['payload' => $row['payload']], ['HTTP_X-SOCKET-ID' => $socket]);
        }
    }

    /**
     * @When I want to send topflytech tcp data to api with socket :socket
     */
    public function sendTcpTopflytechDataToApi($socket)
    {
        $this->post('/api/tracker/topflytech/tcp', $this->fillData, ['HTTP_X-SOCKET-ID' => $socket]);
    }

    /**
     * @When I want to connect to topflytech udp server
     */
    public function connectToTopflytechUdpServer()
    {
        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
            $host = getenv('TCP_SERVER_HOST');
            $port = getenv('UDP_TOPFLYTECH_SERVER_PORT');
            $message = 'ping';
            socket_sendto($socket, $message, strlen($message), 0, $host, $port);
            $result = socket_recvfrom($socket, $response, 12, 0, $host, $port);
            echo "Received $result bytes with response: '$response' from $host port $port";

            if ($response != 'Server error') {
                throw new \Exception('Unexpected response from server');
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        } finally {
            socket_close($socket);
        }

        return true;
    }
}
