<?php

namespace App\EventListener\Migration;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class MigrationEventListener implements EventSubscriber
{
    private $env;

    public function __construct($env)
    {
        $this->env = $env;
    }

    public function getSubscribedEvents()
    {
        return ['postGenerateSchema'];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        if ($this->env !== 'test') {
            $schema = $args->getSchema();
            if (!$schema->hasNamespace('public')) {
                $schema->createNamespace('public');
            }
        }
    }
}