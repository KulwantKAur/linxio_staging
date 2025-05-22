<?php

namespace App\Command;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:invoice:status', description: 'Change invoices status and account status')]
class InvoiceStatusCommand extends Command
{
    public function __construct(
        private EntityManager $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Invoice[] $invoices */
        $invoices = $this->em->getRepository(Invoice::class)->findOldNotPaid(Invoice::OVERDUE_AFTER_DAYS);

        foreach ($invoices as $invoice) {
            $invoice->setStatus(Invoice::STATUS_OVERDUE);
            $this->em->flush();
        }

        return Command::SUCCESS;
    }
}