<?php

namespace App\Controller;

use App\Util\CountryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CountryController extends BaseController
{

    public function __construct()
    {
    }

    #[Route('/country/list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->viewItem(CountryHelper::COUNTRIES);
    }
}
