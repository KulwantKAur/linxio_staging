<?php

namespace App\EventListener\Document;

use App\Entity\DocumentRecord;
use FOS\ElasticaBundle\Persister\ObjectPersister;

class DocumentRecordEntityListener
{
    public function __construct(private readonly ObjectPersister $objectPersisterDocument)
    {
    }

    public function postPersist(DocumentRecord $documentRecord)
    {
        $this->objectPersisterDocument->replaceOne($documentRecord->getDocument());
    }

    public function postUpdate(DocumentRecord $documentRecord)
    {
        $this->objectPersisterDocument->replaceOne($documentRecord->getDocument());
    }
}