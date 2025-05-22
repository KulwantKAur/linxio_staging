<?php

namespace App\Controller;

use App\Entity\DigitalForm;
use App\Entity\DigitalFormAnswer;
use App\Entity\Permission;
use App\Events\DigitalForm\DigitalFormEvent;
use App\Response\PdfResponse;
use App\Service\DigitalForm\DigitalFormAnswerService;
use App\Service\DigitalForm\DigitalFormAnswerValidator;
use App\Service\DigitalForm\DigitalFormScheduleService;
use App\Service\DigitalForm\DigitalFormService;
use App\Service\DigitalForm\Entity\RawAnswer;
use App\Service\PdfService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/digital-form')]
class DigitalFormController extends BaseController
{
    private $pdfService;
    private ReportService $reportService;

    public function __construct(PdfService $pdfService, ReportService $reportService)
    {
        $this->pdfService = $pdfService;
        $this->reportService = $reportService;
    }

    #[Route('/form', methods: ['GET'])]
    public function getListDigitalForm(DigitalFormService $formService): JsonResponse
    {
//        $this->denyAccessUnlessGranted(Permission::DIGITAL_FORM_LIST, DigitalForm::class);
        $rows = $formService->getActiveForms($this->getUser()->getTeam());

        return $this->viewItemsArray($rows);
    }

    #[Route('/form/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDigitalForm(
        $id,
        DigitalFormService $formService,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {
        $entity = $formService->getDigitalFormById((int)$id, $this->getUser()->getTeam());

        if ($entity === null) {
            throw new NotFoundHttpException(
                $translator->trans('digitalForm.entityDigitalFormNotFound', ['%id%' => $id])
            );
        }

        if ($entity->isTypeInspection()) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_INSPECTION_FORM_LIST, DigitalForm::class);
        }

        $vehicle = $this->getUser()->getVehicle();
        if ($vehicle !== null) {
            $eventDispatcher->dispatch(new DigitalFormEvent($entity, $vehicle), DigitalFormEvent::FORM_GET);
        }

        return $this->viewItem($entity, ['steps']);
    }

    #[Route('/form', methods: ['POST'])]
    public function createDigitalForm(Request $request, DigitalFormService $formService): JsonResponse
    {
        if ($request->request->get('type', null) === DigitalForm::TYPE_INSPECTION) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_INSPECTION_FORM_CREATE, DigitalForm::class);
        }
        try {
            $entity = $formService->createForm($request, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($entity);
    }

    #[Route('/form/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function editDigitalForm(
        $id,
        Request $request,
        DigitalFormService $formService,
        TranslatorInterface $translator
    ): JsonResponse {
        $entity = $formService->getDigitalFormById((int)$id, $this->getUser()->getTeam());
        if ($entity === null) {
            throw new NotFoundHttpException(
                $translator->trans('digitalForm.entityDigitalFormNotFound', ['%id%' => $id])
            );
        }

        if ($entity->isTypeInspection()) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_INSPECTION_FORM_EDIT, DigitalForm::class);
        }

        try {
            $entity = $formService->editForm($request, $this->getUser(), $entity);
        } catch (\Exception $e) {
            return $this->viewException($e, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($entity);
    }

    #[Route('/form/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteDigitalForm(
        $id,
        DigitalFormService $formService,
        TranslatorInterface $translator
    ): JsonResponse {
        $entity = $formService->getDigitalFormById((int)$id, $this->getUser()->getTeam());
        if ($entity === null) {
            throw new NotFoundHttpException(
                $translator->trans('digitalForm.entityDigitalFormNotFound', ['%id%' => $id])
            );
        }

        if ($entity->isTypeInspection()) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_INSPECTION_FORM_DELETE, DigitalForm::class);
        }

        try {
            $formService->deleteForm($entity);
        } catch (\Exception $e) {
            return $this->viewException($e, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/form/{id}/restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function restoreDigitalForm(
        $id,
        DigitalFormService $formService,
        TranslatorInterface $translator
    ): JsonResponse {
        $entity = $formService->getDigitalFormById((int)$id, $this->getUser()->getTeam());
        if ($entity === null) {
            throw new NotFoundHttpException(
                $translator->trans('digitalForm.entityDigitalFormNotFound', ['%id%' => $id])
            );
        }

        if ($entity->isTypeInspection()) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_INSPECTION_FORM_DELETE, DigitalForm::class);
        }

        try {
            $formService->restoreForm($entity);
        } catch (\Exception $e) {
            return $this->viewException($e, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($entity);
    }

    #[Route('/schedule-scope', methods: ['GET'])]
    public function getScheduleScope(DigitalFormScheduleService $scheduleService): JsonResponse
    {
        return $this->viewItem($scheduleService->getScope());
    }

    #[Route('/answer/{id}/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getAnswer(
        $id,
        $type,
        DigitalFormAnswerService $answerService,
        TranslatorInterface $translator
    ) {
//        $this->denyAccessUnlessGranted(Permission::DIGITAL_FORM_ANSWER_VIEW, DigitalForm::class);
        /** @var DigitalFormAnswer $answer */
        $answer = $answerService->getDigitalFormAnswerById((int)$id);
        if ($answer === null) {
            throw new NotFoundHttpException(
                $translator->trans('digitalForm.entityDigitalFormAnswerNotFound', ['%id%' => $id])
            );
        }

        if ($answer->getUser()->getTeamId() !== $this->getUser()->getTeamId()) {
            throw new AccessDeniedException($translator->trans('general.access_denied'));
        }

        switch ($type) {
            case 'pdf':
                $pdf = $this->pdfService->getDigitalFormAnswerPdf($answer);

                return new PdfResponse($pdf);
            default:
                return $this->viewItem(
                    $answer,
                    ['user', 'vehicle', 'form', 'answers', 'file', 'additionalFile', 'steps', 'isPass', 'statusRatio']
                );
        }
    }

    #[Route('/answer', methods: ['POST'])]
    public function createAnswer(
        Request $request,
        DigitalFormService $formService,
        DigitalFormAnswerService $answerService,
        DigitalFormAnswerValidator $answerValidator,
        TranslatorInterface $translator
    ): JsonResponse {
//        $this->denyAccessUnlessGranted(Permission::DIGITAL_FORM_ANSWER_CREATE, DigitalForm::class);
        try {
            $formId = (int)$request->request->get('formId', 0);
            $vehicleId = (int)$request->request->get('vehicleId', 0);
            $form = $formService->getDigitalFormById($formId, $this->getUser()->getTeam());
            if ($form === null) {
                throw new NotFoundHttpException(
                    $translator->trans('digitalForm.entityDigitalFormNotFound', ['%id%' => $formId])
                );
            }

            // `answer` is hash (stepId => answerValue)
            $rawAnswer = new RawAnswer($request->request->all('data') ?? [], $request->files->all('data') ?? []);

            $answerValidator->process($form, $rawAnswer);
            if ($answerValidator->isValid()) {
                $answer = $answerService->createAnswer(
                    $form,
                    $this->getUser(),
                    $answerValidator->getValidAnswer(),
                    $vehicleId
                );
            } else {
                return $this->viewError($answerValidator->getErrors(), JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($answer);
    }

    #[Route('/report/vehicle-inspection/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getReportVehicleInspection(string $type, Request $request)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_VEHICLE_INSPECTION)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/schedule', methods: ['GET'])]
    public function getListSchedule(Request $request, DigitalFormScheduleService $scheduleService): JsonResponse
    {
        try {
            if ($request->query->get('type', null) === DigitalForm::TYPE_INSPECTION) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_INSPECTION_FORM_LIST, DigitalForm::class);
            }
            $params = $request->query->all();
            $params = $this->handleRangeParams($params);

            $rows = $scheduleService->getScheduleList($this->getUser(), $params);
        } catch (\Exception $e) {
            return $this->viewException($e, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($rows);
    }

    #[Route('/schedule/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getSchedule(
        $id,
        DigitalFormScheduleService $scheduleService,
        TranslatorInterface $translator
    ): JsonResponse {
        $entity = $scheduleService->getScheduleById((int)$id);
        if ($entity === null) {
            throw new NotFoundHttpException(
                $translator->trans('digitalForm.entityDigitalFormScheduleNotFound', ['%id%' => $id])
            );
        }

        if ($entity->getDigitalForm()->isTypeInspection()) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_INSPECTION_FORM_LIST, DigitalForm::class);
        }

        return $this->viewItem($entity, ['form', 'steps', 'creator', 'recipients']);
    }

    private function handleRangeParams(array $params): array
    {
        if (isset($params['startDate']) && isset($params['endDate'])) {
            $params['date'] = ['gte' => $params['startDate']];
            $params['date']['lte'] = $params['endDate'];
        }
        if (isset($params['startVehicleCount']) && isset($params['endVehicleCount'])) {
            $params['vehicleCount'] = ['gte' => $params['startVehicleCount']];
            $params['vehicleCount']['lte'] = $params['endVehicleCount'];
        }

        return $params;
    }

    #[Route('/inspections-list', methods: ['GET'])]
    public function getInspectionList(
        DigitalFormScheduleService $scheduleService,
        EntityManager $entityManager
    ): JsonResponse {
//        $this->denyAccessUnlessGranted(Permission::DIGITAL_FORM_VIEW, DigitalForm::class);

        $list = [];
        $user = $this->getUser();
        $vehicle = $user->getVehicle();

        if ($vehicle) {
            $list = $scheduleService->getInspectionFormsList($user);
        }

        return $this->viewItemsArray($list, ['steps']);
    }

    #[Route('/form-inspection', methods: ['GET'])]
    public function getDigitalFormInspection(
        DigitalFormScheduleService $scheduleService,
        EntityManager $entityManager
    ): JsonResponse {
//        $this->denyAccessUnlessGranted(Permission::DIGITAL_FORM_VIEW, DigitalForm::class);

        $entity = null;
        $user = $this->getUser();
        $vehicle = $user->getVehicle();

        if ($vehicle) {
            $entity = $scheduleService->getInspectionForm($user, [DigitalForm::INSPECTION_PERIOD_EVERY_TIME]);
        }

        return $this->viewItem($entity, ['steps']);
    }
}