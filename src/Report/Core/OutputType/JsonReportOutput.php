<?php

namespace App\Report\Core\OutputType;

use App\Controller\BaseController;
use App\Entity\User;
use App\Enums\FileExtension;
use App\Report\Core\DataType\DataWithTotal;
use App\Report\Core\Interfaces\ReportOutputInterface;
use App\Report\Core\ResponseType\ArrayResponse;
use App\Report\ReportBuilder;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonReportOutput implements ReportOutputInterface
{
    protected PaginatorInterface $paginator;

    public array $data = [];

    /**
     * JsonReportOutput constructor.
     * @param PaginatorInterface $paginator
     */
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return FileExtension::JSON;
    }

    /**
     * @param ReportBuilder $reportBuilder
     * @return JsonResponse
     */
    public function create(ReportBuilder $reportBuilder, User $user): JsonResponse
    {
        $data = $reportBuilder->getJson();

        if ($data instanceof Pagerfanta) {
            $pagination = [
                'page' => $data->getCurrentPage(),
                'limit' => $data->getMaxPerPage(),
                'total' => $data->getNbResults(),
                'data' => $data->getCurrentPageResults(),
            ];
            $firstItem = $data->count() ? $data->getCurrentPageResults() : null;
        } elseif ($data instanceof ArrayResponse) {
            $pagination = $data->getData();
            $firstItem = $data->getFirstItem();
        } elseif ($data instanceof SlidingPagination) {
            $firstItem = $data->count() ? $data->current() : null;
            $pagination = $data;
        } elseif ($data instanceof DataWithTotal) {
            $firstItem = $data->total;
            $pagination = $data->data;
        } else {
            $pagination = $this->paginator->paginate($data, $reportBuilder->page, $reportBuilder->limit);
            $firstItem = $pagination->count() ? $pagination->current() : null;
        }

        return BaseController::viewItem($pagination, [], 200, ['total' => $firstItem]);
    }
}