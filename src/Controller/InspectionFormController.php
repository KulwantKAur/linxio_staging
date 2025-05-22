<?php

namespace App\Controller;

use App\Entity\InspectionForm;
use App\Entity\InspectionFormData;
use App\Entity\Permission;
use App\Entity\Team;
use App\Entity\Vehicle;
use App\Service\InspectionForm\InspectionFormService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class InspectionFormController extends BaseController
{
    private $inspectionFormService;
    private $translator;
    private EntityManager $em;

    public function __construct(InspectionFormService $inspectionFormService, TranslatorInterface $translator, EntityManager $em)
    {
        $this->inspectionFormService = $inspectionFormService;
        $this->translator = $translator;
        $this->em = $em;
    }

    #[Route('/inspection-form', methods: ['GET'])]
    public function getForm(Request $request): JsonResponse
    {
        try {
            $vehicleId = $request->query->get('vehicleId');
            $form = $this->inspectionFormService->getForm($this->getUser(), $vehicleId);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($form, InspectionForm::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/inspection-form/{id}/vehicle/{vehicleId}/fill', requirements: ['id' => '\d+', 'vehicleId' => '\d+'], methods: ['POST'])]
    public function fillForm(Request $request, $id, $vehicleId): JsonResponse
    {
        $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
        $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
        $this->denyAccessUnlessGranted(Permission::INSPECTION_FORM_FILL, InspectionForm::class);

        try {
            $formData = $this->inspectionFormService->fillForm(
                $id,
                $vehicleId,
                $this->getUser(),
                $request->request->all(),
                $request->files->all()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($formData, InspectionFormData::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/inspection-form/filled/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getFilledForm(Request $request, $id): JsonResponse
    {
        $formData = $this->em->getRepository(InspectionFormData::class)->find($id);
        $this->denyAccessUnlessGranted(null, $formData->getTeam());
        $this->denyAccessUnlessGranted(Permission::INSPECTION_FORM_FILLED, InspectionForm::class);

        try {
            $formData = $this->inspectionFormService->getFilledForm($id, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($formData, InspectionFormData::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/inspection-form/list', methods: ['GET'])]
    public function getFormList(Request $request): JsonResponse
    {
        try {
            if ($request->query->get('vehicleId')) {
                $vehicle = $this->em->getRepository(Vehicle::class)->find($request->query->get('vehicleId'));
                $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            }
            $this->denyAccessUnlessGranted(Permission::INSPECTION_FORM_FILLED, InspectionForm::class);
            $params = $request->query->all();
            if (isset($params['startDate']) && isset($params['endDate'])) {
                $params['date'] = ['gte' => $params['startDate']];
                $params['date']['lte'] = $params['endDate'];
            }

            $formList = $this->inspectionFormService->getFormList($this->getUser(), $params);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($formList);
    }

    #[Route('/inspection-forms', methods: ['GET'])]
    public function getForms(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::INSPECTION_FORM_SET_TEAM, InspectionForm::class);

            $forms = $this->inspectionFormService->getForms();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($forms, InspectionForm::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/inspection-form/{id}/team/{teamId}', requirements: ['id' => '\d+', 'teamId' => '\d+'], methods: ['POST'])]
    public function setClientForm(Request $request, $id, $teamId): JsonResponse
    {
        $team = $this->em->getRepository(Team::class)->find($teamId);
        $this->denyAccessUnlessGranted(null, $team);
        $this->denyAccessUnlessGranted(Permission::INSPECTION_FORM_SET_TEAM, InspectionForm::class);

        try {
            $formData = $this->inspectionFormService->setTeam($team, $id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($formData, InspectionFormData::DEFAULT_DISPLAY_VALUES);
    }
}
