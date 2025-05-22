<?php

namespace App\Command;

use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Entity\Setting;
use App\Exceptions\ValidationException;
use App\Service\Xero\XeroInvoiceService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;

#[AsCommand(name: 'app:invoice:export')]
class InvoiceExportToXeroCommand extends Command
{
    public function __construct(
        private EntityManager $em,
        private XeroInvoiceService $xeroInvoiceService,
        private NotificationEventDispatcher $notificationDispatcher,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Export invoices to Xero');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $invoices = $this->em->getRepository(Invoice::class)->findReadyForExport();
        $clientsWithXeroError = [];
        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
            $billingSetting = $this->em->getRepository(Setting::class)
                ->getSetting(Setting::BILLING, $invoice->getTeam());
            if (!$billingSetting?->getValue()) {
                continue;
            }

            if (!$invoice->getClient()->getXeroClientAccount()?->getXeroContactId()) {
                if (in_array($invoice->getClient()->getId(), $clientsWithXeroError)) {
                    continue;
                }

                $this->notificationDispatcher->dispatch(Event::XERO_INVOICE_CREATION_ERROR, $invoice, null, [
                    'message' => 'No link between this client and Xero contact',
                    'errors' => []
                ]);
                $clientsWithXeroError[] = $invoice->getClient()->getId();

                continue;
            }

            $output->writeln(sprintf('Process Invoice ID %d:', $invoice->getId()));
            try {
                $xeroInvoice = $this->xeroInvoiceService->generateFromInvoice($invoice);
                $invoice = $this->xeroInvoiceService->linkXeroInvoiceToInvoice($xeroInvoice, $invoice);

                $output->writeln(sprintf('Created Xero invoice %s',
                    $invoice->getExtInvoiceId()
                ));

                $this->notificationDispatcher->dispatch(Event::XERO_INVOICE_CREATED, $invoice);
            } catch (ValidationException $exception) {
                $output->writeln($exception->getMessage());
                $output->writeln($exception->getErrors());

                $this->notificationDispatcher->dispatch(Event::XERO_INVOICE_CREATION_ERROR, $invoice, null, [
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getErrors()
                ]);

                $this->makeInvoiceExtIdError($invoice);
            } catch (ClientException $exception) {
                $output->writeln($exception->getMessage());

                $this->notificationDispatcher->dispatch(Event::XERO_INTEGRATION_ERROR, $invoice->getOwnerTeam(), null, [
                    'message' => $exception->getMessage()
                ]);

                $this->makeInvoiceExtIdError($invoice);
            } catch (\Throwable $exception) {
                $output->writeln($exception->getMessage());

                $this->notificationDispatcher->dispatch(Event::XERO_INVOICE_CREATION_ERROR, $invoice, null, [
                    'message' => $exception->getMessage()
                ]);

                $this->makeInvoiceExtIdError($invoice);
                $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            }
        }

        return 0;
    }

    private function makeInvoiceExtIdError(Invoice $invoice)
    {
        $invoice->setExtInvoiceId(Invoice::EXT_INVOICE_ID_ERROR);
        $this->em->flush();
    }
}