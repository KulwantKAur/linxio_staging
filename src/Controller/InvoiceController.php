<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Entity\Permission;
use App\Events\Client\ClientCheckPaidInvoicesEvent;
use App\Exceptions\Billing\MissingPaymentMethodException;
use App\Exceptions\Billing\StripeIntegrationException;
use App\Exceptions\ValidationException;
use App\Response\CsvResponse;
use App\Response\PdfResponse;
use App\Service\Billing\InvoiceService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Payment\PaymentService;
use App\Service\Xero\XeroInvoiceService;
use App\Util\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Stripe\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InvoiceController extends BaseController
{
    public function __construct(
        readonly private PaginatorInterface $paginator,
        readonly private InvoiceService $invoiceService,
        readonly private PaymentService $paymentService,
        readonly private XeroInvoiceService $xeroInvoiceService,
        readonly private NotificationEventDispatcher $notificationDispatcher
    ) {
    }

    #[Route('/billing/invoice/list/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function invoiceList(Request $request, $type, EntityManager $em)
    {
        $this->denyAccessUnlessGranted(Permission::BILLING_INVOICE_VIEW);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $params = $request->query->all();

        try {
            if (!$this->getUser()->isInClientTeam()) {
                if (!isset($params['clientId']) || !$params['clientId']) {
                    throw new \Exception('Missing parameter: clientId');
                }
                $client = $em->getRepository(Client::class)->find($params['clientId']);
                $this->denyAccessUnlessGranted(null, $client->getTeam());
            } else {
                $params['clientId'] = $this->getUser()->getClientId();
            }
            $query = $this->invoiceService->getListForUser((int)$params['clientId'], $params);

            switch ($type) {
                case 'json':
                    $pagination = $this->paginator->paginate($query, $page, $limit);
                    $pagination = PaginationHelper::paginationToEntityArray($pagination);

                    return $this->viewItem($pagination);
                case 'csv':
                    $invoices = $this->invoiceService->prepareExportData($query->getQuery()->execute());

                    return new CsvResponse($invoices);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/invoice/{invoiceId}', requirements: ['invoiceId' => '\d+'], methods: ['GET'])]
    public function getInvoiceDetails(Request $request, $invoiceId)
    {
        try {
            $invoice = $this->invoiceService->getDetails($invoiceId);
            if ($invoice) {
                $this->denyAccessUnlessGranted(Permission::BILLING_INVOICE_VIEW);
                $this->denyAccessUnlessGranted(null, $invoice->getTeam());
            }

            return $this->viewItem($invoice, array_merge(Invoice::DEFAULT_DISPLAY_VALUES, ['details']));
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/invoice/{invoiceId}/pay', requirements: ['invoiceId' => '\d+'], methods: ['POST'])]
    public function pay(Request $request, $invoiceId, EventDispatcherInterface $eventDispatcher)
    {
        $invoice = $this->invoiceService->getDetails($invoiceId);

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_INVOICE_PAY, $invoice);

            if ($invoice->isPaid()) {
                throw new \Exception('Invoice is already paid');
            }

            $paymentStatus = $this->paymentService->payInvoice($invoice);

            if ($paymentStatus == Invoice::STATUS_PAID) {
                $this->xeroInvoiceService->setUserTeam($invoice->getOwnerTeam());
                $payment = $this->xeroInvoiceService->createPayment($invoice);
                $this->xeroInvoiceService->linkXeroPaymentToInvoice($payment, $invoice);

                $this->notificationDispatcher->dispatch(Event::STRIPE_PAYMENT_SUCCESSFUL, $invoice);
                $this->notificationDispatcher->dispatch(Event::PAYMENT_SUCCESSFUL, $invoice);
            } else {
                if ($paymentStatus == Invoice::STATUS_PAYMENT_PROCESSING) {
                    $this->notificationDispatcher->dispatch(Event::STRIPE_PAYMENT_SUCCESSFUL, $invoice);
                    $this->notificationDispatcher->dispatch(Event::PAYMENT_SUCCESSFUL, $invoice);
                }
            }

            $eventDispatcher->dispatch(
                new ClientCheckPaidInvoicesEvent($invoice->getClient()), ClientCheckPaidInvoicesEvent::NAME
            );

            return $this->viewItem($invoice);
        } catch (ValidationException $exception) {
            $this->notificationDispatcher->dispatch(Event::XERO_INVOICE_CREATION_ERROR, $invoice, null, [
                'message' => $exception->getMessage(),
                'errors' => $exception->getErrors()
            ]);

            return $this->viewException($exception);
        } catch (AuthenticationException|StripeIntegrationException $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $invoice->getOwnerTeam(), null, [
                'message' => $exception->getMessage()
            ]);
            return $this->viewException($exception, Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_PAYMENT_FAILED, $invoice, null, [
                'message' => $exception->getMessage()
            ]);
            $this->notificationDispatcher->dispatch(Event::PAYMENT_FAILED, $invoice, null, [
                'message' => $exception->getMessage()
            ]);
            return $this->viewException($exception, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/invoice/{invoiceId}/download', requirements: ['invoiceId' => '\d+'], methods: ['GET'])]
    public function downloadInvoice(Request $request, $invoiceId)
    {
        $invoice = $this->invoiceService->getDetails($invoiceId);

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_INVOICE_VIEW, $invoice);
            $pdf = $this->invoiceService->getInvoicePdf($invoice);

            return new PdfResponse($pdf);
        } catch (\Throwable $exception) {
            return $this->viewException($exception, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/client/{clientId}/invoice/clean', requirements: ['clientId' => '\d+'], methods: ['POST'])]
    public function cleanInvoices(
        Request $request,
        int $clientId,
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher
    ) {

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_INVOICE_CLEAN, Invoice::class);
            $client = $em->getRepository(Client::class)->find($clientId);
            $this->invoiceService->cleanNotSyncedInvoices($client);
            $eventDispatcher->dispatch(new ClientCheckPaidInvoicesEvent($client), ClientCheckPaidInvoicesEvent::NAME);

            return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $exception) {
            return $this->viewException($exception, Response::HTTP_BAD_REQUEST);
        }
    }
}