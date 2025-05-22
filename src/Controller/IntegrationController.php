<?php

namespace App\Controller;

use App\Entity\Integration;
use App\Entity\IntegrationData;
use App\Entity\Team;
use App\Service\Integration\IntegrationService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class IntegrationController extends BaseController
{
    private $translator;
    private $integrationService;
    private EntityManager $em;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        IntegrationService $integrationService,
        EntityManager $em
    ) {
        $this->translator = $translator;
        $this->integrationService = $integrationService;
        $this->em = $em;
    }

    #[Route('/integrations', methods: ['GET'])]
    public function integrationList(Request $request): JsonResponse
    {
        try {
            $integrations = $this->em->getRepository(Integration::class)->findAll();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($integrations);
    }

    #[Route('/integrations/{id}/team/{teamId}/data', requirements: ['id' => '\d+', 'teamId' => '\d+'], methods: ['POST'])]
    public function setIntegrationData(Request $request, $id, $teamId): JsonResponse
    {
        try {
            $team = $this->em->getRepository(Team::class)->find($teamId);
            $this->denyAccessUnlessGranted(null, $team);
            $data = $request->request->all()['data'] ?? [];

            $integrationData = $this->integrationService->updateIntegrationData($data, $id, $team);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationData);
    }

    #[Route('/integrations/{id}/team/{teamId}/scope', requirements: ['id' => '\d+', 'teamId' => '\d+'], methods: ['POST'])]
    public function setIntegrationScope(Request $request, $id, $teamId): JsonResponse
    {
        try {
            $team = $this->em->getRepository(Team::class)->find($teamId);
            $this->denyAccessUnlessGranted(null, $team);
            $scope = $request->request->all()['scope'] ?? [];

            $integrationData = $this->integrationService->updateIntegrationScope($scope, $id, $team);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationData);
    }

    #[Route('/integrations/{id}/team/{teamId}/status', requirements: ['id' => '\d+', 'teamId' => '\d+'], methods: ['POST'])]
    public function setIntegrationStatus(Request $request, $id, $teamId): JsonResponse
    {
        try {
            $team = $this->em->getRepository(Team::class)->find($teamId);
            $this->denyAccessUnlessGranted(null, $team);
            $status = $request->request->get('status', null);

            $integrationData = $this->integrationService->updateIntegrationStatus($status, $id, $team);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationData);
    }

    #[Route('/integrations/{id}/team/{teamId}/data', requirements: ['id' => '\d+', 'teamId' => '\d+'], methods: ['GET'])]
    public function getIntegrationData(Request $request, $id, $teamId): JsonResponse
    {
        try {
            $team = $this->em->getRepository(Team::class)->find($teamId);
            $this->denyAccessUnlessGranted(null, $team);

            $integrationData = $this->em->getRepository(IntegrationData::class)
                ->findByTeamIdAndIntegrationId($teamId, $id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationData);
    }

    #[Route('/integrations/team/{teamId}', requirements: ['id' => '\d+', 'teamId' => '\d+'], methods: ['GET'])]
    public function getIntegrationByTeam(Request $request, $teamId): JsonResponse
    {
        try {
            $team = $this->em->getRepository(Team::class)->find($teamId);
            $this->denyAccessUnlessGranted(null, $team);

            $integrationData = $this->em->getRepository(Integration::class)
                ->getIntegrationsByTeam($team);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($integrationData);
    }
}
