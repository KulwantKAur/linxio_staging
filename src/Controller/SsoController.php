<?php

namespace App\Controller;

use App\Entity\SSOIntegration;
use App\Entity\SSOIntegrationCertificate;
use App\Entity\SSOIntegrationData;
use App\Entity\Team;
use App\Service\SSO\SSOService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sso')]
class SsoController extends BaseController
{
    public function __construct(
        private SSOService             $SSOService,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/integrations', methods: ['GET'])]
    public function integrationList(Request $request): JsonResponse
    {
        try {
            $integrations = $this->em->getRepository(SSOIntegration::class)->findAll();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($integrations);
    }

    #[Route('/integrations/{id}/data', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createIntegrationData(Request $request, int $id): JsonResponse
    {
        try {
            $params = $request->request->all();
            $teamId = $request->request->get('teamId');

            if ($teamId) {
                $team = $this->em->getRepository(Team::class)->find($teamId);
                $this->denyAccessUnlessGranted(null, $team);
                $params['team'] = $team;
            }

            $integrationData = $this->SSOService->createIntegrationData($params, $id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationData);
    }

    #[Route('/integrations/data/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateIntegrationData(Request $request, int $id): JsonResponse
    {
        try {
            $params = $request->request->all();
            $integrationData = $this->em->getRepository(SSOIntegrationData::class)->find($id);

            if (!$integrationData) {
                throw new NotFoundHttpException('Integration data is not found');
            }

            $integrationData = $this->SSOService->updateIntegrationData($params, $integrationData);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationData);
    }

    #[Route('/integrations/data', methods: ['GET'])]
    public function integrationDataList(Request $request): JsonResponse
    {
        try {
            $integrationDataList = $this->em->getRepository(SSOIntegrationData::class)->findAll();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($integrationDataList);
    }

    #[Route('/integrations/data/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getIntegrationData(Request $request, int $id): JsonResponse
    {
        try {
            $integrationData = $this->em->getRepository(SSOIntegrationData::class)->find($id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationData);
    }

    #[Route('/integrations/data/{id}/certificates', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function integrationDataCertificatesList(Request $request, int $id): JsonResponse
    {
        try {
            $integrationData = $this->em->getRepository(SSOIntegrationData::class)->find($id);

            if (!$integrationData) {
                throw new NotFoundHttpException('Integration data is not found');
            }

            $integrationDataCertificates = $this->em->getRepository(SSOIntegrationCertificate::class)
                ->findBy(['integrationData' => $integrationData]);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($integrationDataCertificates);
    }

    #[Route('/integrations/data/{id}/certificates', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createIntegrationDataCertificate(Request $request, int $id): JsonResponse
    {
        try {
            $params = $request->request->all();
            $integrationData = $this->em->getRepository(SSOIntegrationData::class)->find($id);

            if (!$integrationData) {
                throw new NotFoundHttpException('Integration data is not found');
            }

            $integrationDataCertificate = $this->SSOService
                ->createIntegrationDataCertificate($params, $integrationData);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($integrationDataCertificate);
    }

    #[Route('/integrations/data/{id}/certificates/{certificateId}', requirements: ['id' => '\d+', 'certificateId' => '\d+'], methods: ['DELETE'])]
    public function deleteIntegrationDataCertificate(Request $request, int $id, int $certificateId): JsonResponse
    {
        try {
            $integrationData = $this->em->getRepository(SSOIntegrationData::class)->find($id);

            if (!$integrationData) {
                throw new NotFoundHttpException('Integration data is not found');
            }

            $integrationDataCertificate = $this->SSOService
                ->deleteIntegrationDataCertificate($certificateId, $integrationData);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }
}
