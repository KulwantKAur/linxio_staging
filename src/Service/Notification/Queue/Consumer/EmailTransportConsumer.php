<?php

namespace App\Service\Notification\Queue\Consumer;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Notification\Message;
use App\Mailer\MailSender;
use App\Mailer\Render\TwigEmailRender;
use App\Service\Notification\AttachmentService;
// use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class EmailTransportConsumer implements ConsumerInterface
{
    use CommandLoggerTrait;

    public function __construct(
        private readonly EntityManager $em,
        private readonly MailSender $mailSender,
        private readonly LoggerInterface $logger,
        private readonly TwigEmailRender $emailRender,
        private readonly AttachmentService $attachmentService,
    ) {}

    public function execute(AMQPMessage $msg): void
    {
        $data = json_decode($msg->getBody(), true);
        $messageId = $data['id'] ?? null;

        if (!$messageId) {
            $this->logger->warning('EmailConsumer: Invalid payload, missing ID.');
            return;
        }

        try {
            /** @var Message|null $message */
            $message = $this->em->getRepository(Message::class)->find($messageId);

            if (!$message) {
                $this->logger->warning("EmailConsumer: Message ID {$messageId} not found.");
                return;
            }

            // Validate Email before doing heavy work
            if (!MailSender::isValidateEmailDomain($message->getRecipient(), $this->em)) {
                $this->logger->warning("EmailConsumer: Invalid domain {$message->getRecipient()}");
                return;
            }

            $renderedEmail = $this->emailRender->render(
                'emails/notification.html.twig',
                [
                    'subject' => $message->getBodySubject(),
                    'body' => $message->getBodyMessage(),
                    'emailName' => $message->getTeam()?->getEmailName(),
                    'logoPath' => $message->getTeam()?->getLogoPath(),
                    'timezone' => $message->getTimezone()
                ]
            );

            $attachments = $this->attachmentService->getAttachments($message->getEventLog());

            $this->mailSender->sendEmail(
                [$message->getRecipient()],
                $renderedEmail,
                $message->getSender(),
                [],
                null,
                $attachments
            );
        } catch (\Throwable $e) {
            $this->logger->error('EmailConsumer Error: ' . $e->getMessage());
            $this->logException($e);
        } finally {
            // Free memory
            $this->em->clear();
        }
    }
}
