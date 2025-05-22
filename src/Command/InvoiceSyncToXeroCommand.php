<?php

namespace App\Command;

use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Exceptions\ValidationException;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Xero\XeroInvoiceService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:invoice:sync-to-xero')]
class InvoiceSyncToXeroCommand extends Command
{
    public function __construct(
        private XeroInvoiceService $xeroInvoiceService,
        private EntityManager $em,
        private NotificationEventDispatcher $notificationDispatcher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Get status in Linxio and if invoice is Paid mark it paid in Xero');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $invoices = $this->em->getRepository(Invoice::class)->findPaidWithoutXeroPayment();

        foreach ($invoices as $invoice) {
            $output->writeln(sprintf('Process Invoice ID %d:', $invoice->getId()));
            try {
                $this->xeroInvoiceService->syncStatusToXero($invoice);
            } catch (ValidationException $exception) {
                $output->writeln($exception->getMessage());
                $output->writeln($exception->getErrors());

                $this->notificationDispatcher->dispatch(Event::XERO_PAYMENT_CREATION_ERROR, $invoice, null, [
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getErrors()
                ]);
            } catch (ClientException|\Exception $exception) {
                $output->writeln($exception->getMessage());

                $this->notificationDispatcher->dispatch(Event::XERO_INTEGRATION_ERROR, $invoice->getOwnerTeam(), null, [
                    'message' => $exception->getMessage()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
