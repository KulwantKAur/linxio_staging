<?php

namespace App\Command;

use App\Entity\Client;
use App\Entity\EventLog\EventLog;
use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Entity\Setting;
use App\Entity\Team;
use App\Enums\EntityHistoryTypes;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:invoice:overdue')]
class InvoiceOverdueCommand extends Command
{
    public function __construct(
        readonly private EntityManager $em,
        readonly private NotificationEventDispatcher $notificationDispatcher,
        readonly private EntityHistoryService $entityHistoryService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Find overdue invoices and change client status');
        $this->addOption('teamId', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $invoiceOverdueEvent = $this->em->getRepository(Event::class)->findOneBy(['name' => Event::INVOICE_OVERDUE]);
        $invoiceOverduePartiallyBlockedEvent = $this->em->getRepository(Event::class)
            ->findOneBy(['name' => Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED]);
        $invoiceOverdueBlockedEvent = $this->em->getRepository(Event::class)
            ->findOneBy(['name' => Event::INVOICE_OVERDUE_BLOCKED]);
        $teamId = $input->getOption('teamId');

        if ($teamId) {
            $invoices = $this->em->getRepository(Invoice::class)->findOverdueInvoicesByClientTeam($teamId);
        } else {
            $invoices = $this->em->getRepository(Invoice::class)->findOverdueInvoices();
        }

        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
            $output->writeln(sprintf('Process Invoice ID %d:', $invoice->getId()));
            try {
                $billingSetting = $this->em->getRepository(Setting::class)
                    ->getSetting(Setting::BILLING, $invoice->getClient());
                if (!$billingSetting?->getValue()) {
                    continue;
                }

                $client = $invoice->getClient();
                $dueAt = Carbon::instance($invoice->getDueAt());

                //check only working clients
                if (!in_array($client->getStatus(), [
                    Client::STATUS_CLIENT,
                    Client::STATUS_PARTIALLY_BLOCKED_BILLING
                ])) {
                    continue;
                }

                $eventLog = $this->em->getRepository(EventLog::class)->findOneBy([
                    'event' => $invoiceOverdueEvent,
                    'entityId' => $invoice->getId()
                ]);
                if (!$eventLog) {
                    $this->notificationDispatcher->dispatch(Event::INVOICE_OVERDUE, $invoice);
                    continue;
                }

                $eventLog = $this->em->getRepository(EventLog::class)->findOneBy([
                    'event' => $invoiceOverduePartiallyBlockedEvent,
                    'entityId' => $invoice->getId()
                ]);

                // Partially block
                if (!$eventLog || $client->getStatus() != Client::STATUS_PARTIALLY_BLOCKED_BILLING) {
                    if (($dueAt->diffInDays(new \DateTime()) > Invoice::OVERDUE_PARTIALLY_BLOCKED_DAYS && $dueAt->diffInDays(new \DateTime()) < Invoice::OVERDUE_BLOCKED_DAYS)
                    || ($client->getContractMonths() && $dueAt->diffInDays(new \DateTime()) > Invoice::OVERDUE_BLOCKED_DAYS)
                    ) {
                        $client->setStatus(Client::STATUS_PARTIALLY_BLOCKED_BILLING);
                        $this->entityHistoryService
                            ->create($client, $client->getStatus(), EntityHistoryTypes::CLIENT_STATUS);

                        $this->em->flush();

                        $this->notificationDispatcher->dispatch(Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED, $invoice);
                        continue;
                    }
                }

                $eventLog = $this->em->getRepository(EventLog::class)->findOneBy([
                    'event' => $invoiceOverdueBlockedEvent,
                    'entityId' => $invoice->getId()
                ]);

                // Full block
                if (!$client->getContractMonths() && !$eventLog && $dueAt->diffInDays(new \DateTime()) > Invoice::OVERDUE_BLOCKED_DAYS) {

                    $invoice->getClient()->setStatus(Client::STATUS_BLOCKED_BILLING);
                    $this->entityHistoryService
                        ->create($client, $client->getStatus(), EntityHistoryTypes::CLIENT_STATUS);

                    $this->em->flush();

                    $this->notificationDispatcher->dispatch(Event::INVOICE_OVERDUE_BLOCKED, $invoice);
                    continue;
                }
            } catch (\Throwable $exception) {
                $output->writeln($exception->getTraceAsString());
                $output->writeln($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
