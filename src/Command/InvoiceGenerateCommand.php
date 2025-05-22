<?php

namespace App\Command;

use App\Entity\Client;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\User;
use App\Service\Billing\BillingService;
use App\Service\Billing\InvoiceService;
use App\Util\ExceptionHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:invoice:generate')]
class InvoiceGenerateCommand extends Command
{
    public function __construct(
        private EntityManager $em,
        private BillingService $billingService,
        private InvoiceService $invoiceService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generate invoices for all client for selected period');
        $this->addOption('date', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //We should fetch correct user, not super admin
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findByRole(Role::ROLE_SUPER_ADMIN);

        if ($input->getOption('date')) {
            $startDate = Carbon::parse($input->getOption('date'))->firstOfMonth();
            $endDate = Carbon::parse($input->getOption('date'))->lastOfMonth();

        } else {
            $startDate = Carbon::now()->subMonthNoOverflow()->firstOfMonth();
            $endDate = Carbon::now()->subMonthNoOverflow()->lastOfMonth()->endOfDay();
        }

        $prepaymentPeriodStart = $startDate->copy()->modify('first day of next month');
        $prepaymentPeriodEnd = $endDate->copy()->modify('last day of next month')->endOfDay();

        $query = $this->billingService->getClientsBillingPayments([
            'startDate' => $startDate,
            'endDate' => $endDate
        ], $user);
        $billingItems = $this->billingService->updateBillingItemsFormat($query->execute()->fetchAllAssociative());

        $query = $this->billingService->getClientsBillingForPrepayment([
            'startDate' => $prepaymentPeriodStart,
            'endDate' => $prepaymentPeriodStart->copy()->addMinute(),
            'period' => 1
        ], $user);
        $prepaymentBillingItems = $this->billingService->updateBillingItemsFormat($query->execute()->fetchAllAssociative());

        $prepaymentBillingItems = array_combine(
            array_column($prepaymentBillingItems, 'team_id'), $prepaymentBillingItems
        );

        $createdInvoicesCount = 0;

        foreach ($billingItems as $item) {
            try {
                if ($this->invoiceService->exists($item, $prepaymentPeriodStart, $prepaymentPeriodEnd)) {
                    continue;
                }
                $isNewClient = $this->invoiceService->isNewClient($item['client_id']);
                $client = $this->em->getRepository(Client::class)->find($item['client_id']);
                $billingSetting = $this->em->getRepository(Setting::class)
                    ->getSetting(Setting::BILLING, $client->getTeam());

                // if client created current month - don't create invoice
                if ($client->getCreatedAt() > $endDate) {
                    continue;
                }
                // if billing setting enabled current month - don't create invoice
                if ($billingSetting?->getValue() && $billingSetting->getUpdatedAt() && $billingSetting->getUpdatedAt() > $prepaymentPeriodStart) {
                    continue;
                }

                $prepaymentInvoice = $this->invoiceService
                    ->createPrepayment($item, $prepaymentBillingItems, $prepaymentPeriodStart, $prepaymentPeriodEnd);
                if ($isNewClient) {
                    $invoiceOptions = [
                        'client_id' => $item['client_id'],
                    ];
                    $invoice = $this->invoiceService
                        ->createInvoice($invoiceOptions, $prepaymentInvoice, $startDate, $endDate);
                } else {
                    $invoice = $this->invoiceService->createInvoice($item, $prepaymentInvoice, $startDate, $endDate);
                }

                if ($invoice) {
                    $createdInvoicesCount++;
                    $output->writeln(sprintf('Created invoice ID %d', $invoice->getId()));
                }

                if ($prepaymentInvoice) {
                    $output->writeln(sprintf('Created prepayment ID %d', $prepaymentInvoice->getId()));
                }
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln('Client: ' . $item['client_id']);
                $output->writeln($e->getTraceAsString());
                $this->logger->error(ExceptionHelper::convertToJson($e), [$this->getName()]);
            }
        }

        if ($createdInvoicesCount) {
            $output->writeln(sprintf('%d Invoices successfully created!', $createdInvoicesCount));
        } else {
            $output->writeln('No invoices created');
        }

        return 0;
    }
}