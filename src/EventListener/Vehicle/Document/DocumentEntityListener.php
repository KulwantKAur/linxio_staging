<?php

namespace App\EventListener\Vehicle\Document;

use App\Entity\Document;
use App\Entity\DocumentRecord;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DocumentEntityListener
{
    public function postLoad(Document $document, LifecycleEventArgs $args)
    {
        $document->refreshCurrentActiveRecord();

        return $document;
    }
}