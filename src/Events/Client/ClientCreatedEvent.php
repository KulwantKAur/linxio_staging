<?php

namespace App\Events\Client;

use App\Entity\Client;
use Symfony\Contracts\EventDispatcher\Event;

class ClientCreatedEvent extends Event
{
    const NAME = 'app.event.client.created';
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}