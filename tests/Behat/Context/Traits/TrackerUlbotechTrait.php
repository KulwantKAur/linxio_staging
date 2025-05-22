<?php

namespace App\Tests\Behat\Context\Traits;

use Behat\Gherkin\Node\TableNode;

trait TrackerUlbotechTrait
{
    /**
     * @When I want to connect to ulbotech tcp server
     */
    public function connectToUlbotechTcpServer()
    {
        try {
            $fp = fsockopen(getenv('TCP_SERVER_HOST'), getenv('TCP_ULBOTECH_SERVER_PORT'), $errno, $errstr, 10);

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
     * @Given There are following tracker payload from ulbotech tracker with socket :socket:
     */
    public function thereAreaFollowingUlbotechTrackerPayload($socket, TableNode $tableNode)
    {
        foreach ($tableNode as $row) {
            $this->post('/api/tracker/ulbotech/tcp', ['payload' => $row['payload']], ['HTTP_X-SOCKET-ID' => $socket]);
        }
    }

    /**
     * @When I want to send ulbotech tcp data to api with socket :socket
     */
    public function sendTcpUlbotechDataToApi($socket)
    {
        $this->post('/api/tracker/ulbotech/tcp', $this->fillData, ['HTTP_X-SOCKET-ID' => $socket]);
    }
}
