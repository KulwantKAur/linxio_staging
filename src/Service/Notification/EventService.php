<?php

namespace App\Service\Notification;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventService
{
    private $eventRepository;
    protected $translator;

    /**
     * EventService constructor.
     * @param EntityManager $em
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManager $em, TranslatorInterface $translator)
    {
        $this->eventRepository = $em->getRepository(Event::class);
        $this->translator = $translator;
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function getUserEvents(array $params)
    {
        $types = $params['type'] ?? Event::ALLOWED_TYPES;
        $events = $this->eventRepository->getEvents($types);
        $entityToTranslate = EventLog::class;
        $entityName = class_exists($entityToTranslate)
            ? strtolower((new \ReflectionClass($entityToTranslate))->getShortName()) : $entityToTranslate;

        return array_map(
            function (Event $event) use ($entityName) {
                if (!empty($event->getHeaderByEvent())) {
                    $header = [];
                    foreach ($event->getHeaderByEvent() as $key => $value) {
                        $header[$key] = $this->translator->trans($entityName . '.' . $value, [], 'entities');
                    }
                    $event->setHeaderByEvent($header);
                }

                return $event;
            },
            $events
        );
    }
}
