<?php

namespace App\Controller;

use App\Entity\BaseEntity;
use App\Entity\TimeZone;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TimeZoneController extends BaseController
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    #[Route('/timezones', methods: ['GET'])]
    public function getTimeZones(EntityManager $em): JsonResponse
    {
        $timezones = $em->getRepository(TimeZone::class)->findAll();

        foreach ($timezones as $key => $row) {
            $offsetValues[$key]  = $row->getOffset();
            $nameValues[$key] = $row->getName();
        }
        array_multisort($offsetValues, SORT_ASC, $nameValues, SORT_ASC, $timezones);

        return $this->viewItemsArray($timezones);
    }
}