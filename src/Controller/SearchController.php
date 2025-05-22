<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\User;
use App\Service\ElasticSearch\FullSearch;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends BaseController
{
    private $fullSearch;

    public function __construct(FullSearch $fullSearch)
    {
        $this->fullSearch = $fullSearch;
    }

    #[Route('/search', methods: ['GET'])]
    public function fullSearch(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::FULL_SEARCH, User::class);
        $query = $request->query->get('query');

        $env = $this->getParameter('kernel.environment');
        $result = $this->fullSearch->search($query, $this->getUser(), $env);

        return $this->viewItemsArray($result);
    }
}