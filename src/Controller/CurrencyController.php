<?php

namespace App\Controller;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrencyController extends BaseController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/currencies', methods: ['GET'])]
    public function currencies(Request $request): JsonResponse
    {
        try {
            $currencies = $this->em->getRepository(Currency::class)->findAll();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($currencies);
    }
}
