<?php

namespace App\Events\Client;

use App\Entity\Client;
use Symfony\Contracts\EventDispatcher\Event;

class ClientContractChangedEvent extends Event
{
    const NAME = 'app.event.client.contract.changed';
    protected Client $client;
    protected ?Client $clientOld;

    public function __construct(Client $client, ?Client $clientOld)
    {
        $this->client = $client;
        $this->clientOld = $clientOld;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getClientOld(): ?Client
    {
        return $this->clientOld;
    }
}