<?php

namespace App\Mailer;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Notification\TemplateSet;
use App\Entity\PlatformSetting;
use App\Entity\ResetPassword;
use App\Entity\ScheduledReport;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Mailer\Render\RenderedEmail;
use App\Mailer\Render\TwigEmailRender;
use App\Util\StringHelper;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailSender
{
    private $mailer;
    private $from;
    private $appFrontUrl;
    private TranslatorInterface $translator;
    use CommandLoggerTrait;

    public const EMAIL_TRANSLATE_DOMAIN = 'email';

    public function __construct(
        $from,
        $appFrontUrl,
        MailerInterface $mailer,
        private readonly TwigEmailRender $emailRender,
        TranslatorInterface $translator,
        private readonly EntityManager $em,
        private readonly Logger $logger
    ) {
        $this->from = $from;
        $this->appFrontUrl = $appFrontUrl;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }


    public function resetPassword(User $user, string $token, ResetPassword $resetPassword, int $ttl): void
    {
        $langSetting = $user->getSettingByName(Setting::LANGUAGE_SETTING);
        $this->translator->setLocale($langSetting ? $langSetting->getValue() : Setting::LANGUAGE_SETTING_DEFAULT_VALUE);
        $path = $this->em->getRepository(TemplateSet::class)->getByTeam($user->getTeam())->getPath();
        $domain = $user->getHostApp() ?? $this->appFrontUrl;

        $renderedEmail = $this->emailRender->render(
            $path . 'emails/reset_password.html.twig',
            [
                'subject' => $this->translator
                    ->trans('reset_password', [], $user->getTeam()->getEmailTranslateFilename()),
                'user' => $user,
                'front_url_reset_password' => $domain . '/set-new-password/' . $token,
                'emailName' => $user->getTeam()->getEmailName(),
                'logoPath' => $user->getTeam()->getLogoPath(),
                'expiredAt' => $resetPassword->getCreatedAt()
                    ->setTimezone(new \DateTimeZone($user->getTimezone()))
                    ->add(new \DateInterval('PT' . $ttl . 'S'))
                    ->format($user->getDateFormatSettingConverted(true))
            ]
        );

        $this->sendEmail([$user->getEmail()], $renderedEmail, $user->getTeam()->getNotificationEmail());
    }

    public function verifyPhone(User $user)
    {
        $domain = $user->getHostApp() ?? $this->appFrontUrl;

        $productName = $user->getProductName() ?? 'Linxio';
        $renderedEmail = $this->emailRender->render(
            'emails/verify_phone.html.twig',
            [
                'subject' => $this->translator->trans(
                    'linxio_cred', ['%product_name%' => $productName],
                    self::EMAIL_TRANSLATE_DOMAIN
                ),
                'product_name' => $productName,
                'user' => $user,
                'company' => $user->getClient() ? $user->getClient()->getName() : ucfirst(Team::TEAM_ADMIN),
                'front_url_verify_phone' => $domain . '/verify-phone/' . $user->getVerifyToken(),
                'emailName' => $user->getTeam()->getEmailName(),
                'logoPath' => $user->getTeam()->getLogoPath()
            ]
        );

        $this->sendEmail([$user->getEmail()], $renderedEmail, $user->getTeam()->getNotificationEmail());
    }

    public function setPassword(User $user)
    {
        $domain = $user->getHostApp() ?? $this->appFrontUrl;
        $productName = $user->getProductName() ?? 'Linxio';

        $renderedEmail = $this->emailRender->render(
            'emails/set_password.html.twig',
            [
                'subject' => $this->translator->trans(
                    'linxio_cred', ['%product_name%' => $productName],
                    self::EMAIL_TRANSLATE_DOMAIN
                ),
                'product_name' => $productName,
                'user' => $user,
                'company' => $user->getClient() ? $user->getClient()->getName() : ucfirst(Team::TEAM_ADMIN),
                'front_url_set_password' => '<a href="' . $domain . '/set-new-password/' . $user->getVerifyToken() . ';setNewPassword=true">'
                    . $domain . '/set-new-password/' . $user->getVerifyToken()
                    . '</a > ',
                'emailName' => $user->getTeam()->getEmailName(),
                'logoPath' => $user->getTeam()->getLogoPath()
            ]
        );

        $this->sendEmail([$user->getEmail()], $renderedEmail, $user->getTeam()->getNotificationEmail());
    }


    public function sendEmail(
        array $to,
        RenderedEmail $renderedEmail,
        $from = null,
        $files = [],
        $replyTo = null,
        $attachments = []
    ): void {

        $to = StringHelper::filterEmailArray($to);

        foreach (array_chunk($to, 45) as $emails) {
            try {
                $email = (new Email())->from($from ?? $this->from)
                    ->to(...$emails)
                    ->subject($renderedEmail->subject())
                    ->html($renderedEmail->body());
                if ($replyTo) {
                    $email->replyTo($replyTo);
                }

                foreach ($files as $file) {
                    $email->addPart(new DataPart(new File($file->getPathname()), $file->getClientOriginalName()));
                }

                foreach ($attachments as $attachment) {
                    $email->addPart($attachment);
                }

                $this->mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                $this->logException($e);
                $this->mailer->send($email);
            }
        }
    }

    public static function getScheduledReportEmailAttachment(ScheduledReport $scheduledReport, $data, $filename = null)
    {
        $filename = $filename ?? 'filename.' . $scheduledReport->getFormat();
        switch ($scheduledReport->getFormat()) {
            case ScheduledReport::FORMAT_CSV:
                return self::getCsvAttachment($data, $filename);
            case ScheduledReport::FORMAT_PDF:
                return self::getPdfAttachment($data, $filename);
            case ScheduledReport::FORMAT_XLSX:
                return self::getXlsxAttachment($data, $filename);
        }
    }

    public static function getCsvAttachment($data, $filename = 'filename.csv')
    {
        return new DataPart($data, $filename, 'application/csv');


//        return (new \Swift_Attachment($data))
//            ->setFilename($filename)
//            ->setContentType('application/csv');
    }

    public static function getPdfAttachment($data, $filename = 'filename.pdf')
    {
        return new DataPart($data, $filename, 'application/pdf');

//        return (new \Swift_Attachment($data))
//            ->setFilename($filename)
//            ->setContentType('application/pdf');
    }

    public static function getXlsxAttachment($data, $filename = 'filename.xlsx')
    {
        return new DataPart($data, $filename, 'application/vnd.ms-excel');

//        return (new \Swift_Attachment($data))
//            ->setFilename($filename)
//            ->setContentType('application/vnd.ms-excel');
    }

    public function sendChangePlanNtf(User $user)
    {
        $client = $user->getClient();
        $cc = $client->getTeam()->getPlatformSettingByTeam()?->getAccountingEmail() ?? PlatformSetting::DEFAULT_ACCOUNTING_EMAIL;

        $to = [];
        $accountManagerEmail = $client->getManager()?->getEmail();
        if ($accountManagerEmail) {
            $to[] = $accountManagerEmail;
        }
        $salesManagerEmail = $client->getSalesManager()?->getEmail();
        if ($salesManagerEmail) {
            $to[] = $salesManagerEmail;
        }
        if (empty($to)) {
            $to = $cc;
        }

        $email = (new Email())->from($from ?? $this->from)
            ->to(...$to)
            ->subject('Change plan request')
            ->text(vsprintf(
                "User %s (%s) from %s (%s/admin/clients/%d) requested subscription plan changes",
                [$user->getFullName(), $user->getEmail(), $client->getName(), $this->appFrontUrl, $client->getId()]
            ));
        if ($cc) {
            $email->cc(is_array($to) ? $cc : null);
        }

        $this->mailer->send($email);
    }

    public static function isValidateEmailDomain(string $email, EntityManager $em): bool
    {
        $conn = $em->getConnection();
        $stmt = $conn->prepare('SELECT domain FROM email_domain_black_list');
        $domains = $stmt->executeQuery()->fetchAllAssociative();

        return !array_filter($domains, fn($domain) => str_ends_with($email, $domain['domain']));
    }
}