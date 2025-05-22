<?php

namespace App\Command;

use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Exceptions\Billing\MissingPaymentMethodException;
use App\Exceptions\Billing\StripeIntegrationException;
use App\Exceptions\ValidationException;
use App\Service\Billing\InvoiceService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Payment\PaymentService;
use App\Service\Xero\XeroInvoiceService;
use Doctrine\ORM\EntityManager;
use Stripe\Exception\AuthenticationException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:invoice:pay')]
class InvoicePaymentCommand extends Command
{
    public function __construct(
        private EntityManager $em,
        private InvoiceService $invoiceService,
        private XeroInvoiceService $xeroInvoiceService,
        private PaymentService $paymentService,
        private NotificationEventDispatcher $notificationDispatcher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Check invoice status in Xero and if it not paid perform Stripe payment');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $invoices = $this->em->getRepository(Invoice::class)->findReadyForPayment();

        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
            $output->writeln(sprintf('Process Invoice ID %d:', $invoice->getId()));
            try {
                $paymentStatus = $this->paymentService->payInvoice($invoice);

                if ($paymentStatus == Invoice::STATUS_PAID) {
                    $payment = $this->xeroInvoiceService->createPayment($invoice);
                    $this->xeroInvoiceService->linkXeroPaymentToInvoice($payment, $invoice);

                    $this->notificationDispatcher->dispatch(Event::STRIPE_PAYMENT_SUCCESSFUL, $invoice);
                    $this->notificationDispatcher->dispatch(Event::PAYMENT_SUCCESSFUL, $invoice);

                    $output->writeln(sprintf('Paid invoice %d', $invoice->getId()));
                } else {
                    if ($paymentStatus == Invoice::STATUS_PAYMENT_ERROR) {
                        throw new \Exception('Error on payment');
                    } else {
                        if ($paymentStatus == Invoice::STATUS_PAYMENT_PROCESSING) {
                            $output->writeln('Payment processing');
                        }
                    }
                }
            } catch (ValidationException $exception) {
                $output->writeln($exception->getMessage());
                $output->writeln($exception->getErrors());

                $this->notificationDispatcher->dispatch(Event::XERO_PAYMENT_CREATION_ERROR, $invoice, null, [
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getErrors()
                ]);
            } catch (AuthenticationException|StripeIntegrationException $exception) {
                $output->writeln($exception->getMessage());
                $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $invoice->getOwnerTeam(), null,
                    [
                        'message' => $exception->getMessage()
                    ]);
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
                $this->notificationDispatcher->dispatch(Event::STRIPE_PAYMENT_FAILED, $invoice, null, [
                    'message' => $exception->getMessage()
                ]);
                $this->notificationDispatcher->dispatch(Event::PAYMENT_FAILED, $invoice, null, [
                    'message' => $exception->getMessage()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}