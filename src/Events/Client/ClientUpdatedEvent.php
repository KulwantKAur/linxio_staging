<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.04.19
 * Time: 15:03
 */

namespace App\Events\Client;


use App\Entity\Client;
use Symfony\Contracts\EventDispatcher\Event;

class ClientUpdatedEvent extends Event
{
    const NAME = 'app.event.client.updated';
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