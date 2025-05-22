<?php

namespace App\Controller;

use App\Entity\EventLog\EventLog;
use App\Entity\User;
use App\Response\CsvResponse;
use App\Service\EventLog\EventLogService;
use App\Service\EventLog\Interfaces\ReportFacadeInterface;
use App\Service\EventLog\Report\EventLogReportService;
use App\Util\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EventLogController extends BaseController
{
    private $eventLogService;
    private $eventLogReportService;
    private $paginator;

    public function __construct(
        EventLogService $eventLogService,
        EventLogReportService $eventLogReportService,
        PaginatorInterface $paginator
    ) {
        $this->eventLogService = $eventLogService;
        $this->eventLogReportService = $eventLogReportService;
        $this->paginator = $paginator;
    }

    #[Route('/event-log/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getEventLog(Request $request, string $type, ReportFacadeInterface $reportFacade)
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $logEvent = $reportFacade->findBy(
                        $this->eventLogReportService->prepareParams($params, $user)
                    );

                    if (null === $logEvent) {
                        throw new NotFoundHttpException();
                    }

                    $page = $request->query->get('page', 1);
                    $limit = $request->query->get('limit', 10);
                    $pagination = $this->paginator->paginate(
                        $logEvent,
                        $page,
                        $limit,
                        [PaginatorInterface::SORT_FIELD_PARAMETER_NAME => '~']
                    );
                    $pagination = PaginationHelper::paginationToEntityArray(
                        $pagination,
                        EventLog::DISPLAYED_VALUES
                    );

                    // TODO: Temporary decision
                    $logEventData = $this->eventLogReportService->getEventLogView(
                        $pagination,
                        $this->eventLogReportService->prepareParams($params, $user),
                        $user
                    );
                    return $this->viewItem($logEventData);
                case 'csv':
                    $logEvent = $reportFacade->findBy(
                        $this->eventLogReportService->prepareParams($params, $user)
                    );

                    if (null === $logEvent) {
                        throw new NotFoundHttpException();
                    }

                    $eventLog = $this->eventLogReportService->getEventLogSqlExportData(
                        $logEvent,
                        $params,
                        $user
                    );

                    return new CsvResponse($eventLog, 200, [], true, ['csv_key_separator' => ',']);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/event-log/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id, EntityManager $em): JsonResponse
    {
        try {
            /** @var EventLog $eventLog */
            $eventLog = $em->getRepository(EventLog::class)->find($id);
            if ($eventLog) {
                $this->denyAccessUnlessGranted(null, $eventLog->getTeam());
                $this->eventLogService->delete($eventLog);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }
}
