<?php

namespace App\Command;

use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Events\Client\ClientCheckPaidInvoicesEvent;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Xero\XeroInvoiceService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'app:invoice:sync-from-xero')]
class InvoiceSyncFromXeroCommand extends Command
{
    public function __construct(
        readonly private XeroInvoiceService $xeroInvoiceService,
        readonly private EntityManager $em,
        readonly private NotificationEventDispatcher $notificationDispatcher,
        readonly private EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Get not Paid invoices from Linxio and check status in Xero. Update status in Linxio if required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $invoices = $this->em->getRepository(Invoice::class)->findNotPaidWithXeroInvoice();

        foreach ($invoices as $invoice) {
            try {
                $paidInvoice = $this->xeroInvoiceService->syncStatusFromXero($invoice);
                if ($paidInvoice) {
                    $output->writeln(sprintf('Added payment for invoice ID: %d', $paidInvoice->getId()));
                    $this->eventDispatcher->dispatch(
                        new ClientCheckPaidInvoicesEvent($invoice->getClient()), ClientCheckPaidInvoicesEvent::NAME
                    );
                }
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
