<?php

namespace App\Service\TrackLink;

use App\Entity\Setting;
use App\Entity\Theme;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\TrackLink;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Exceptions\ValidationException;
use App\Mailer\MailSender;
use App\Mailer\Render\TwigEmailRender;
use App\Service\BaseService;
use App\Service\Device\DeviceService;
use App\Service\Sms\SmsService;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrackLinkService extends BaseService
{
    private $em;
    private $translator;
    private $deviceService;
    private $smsService;
    private $mailSender;
    private $emailRender;

    /**
     * TrackLinkService constructor.
     * @param EntityManager $em
     * @param TranslatorInterface $translator
     * @param DeviceService $deviceService
     * @param SmsService $smsService
     * @param MailSender $mailSender
     * @param TwigEmailRender $emailRender
     */
    public function __construct(
        EntityManager $em,
        TranslatorInterface $translator,
        DeviceService $deviceService,
        SmsService $smsService,
        MailSender $mailSender,
        TwigEmailRender $emailRender
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->deviceService = $deviceService;
        $this->smsService = $smsService;
        $this->mailSender = $mailSender;
        $this->emailRender = $emailRender;
    }

    public function create(array $data, User $currentUser)
    {
        $this->validateCreateData($data, $currentUser);

        $data['createdBy'] = $currentUser;
        $data['vehicle'] = $this->em->getRepository(Vehicle::class)->find($data['vehicleId']);
        $data['dateFrom'] = (new \DateTime($data['dateFrom']))->setTimezone(new \DateTimeZone('UTC'));
        $data['dateTo'] = (new \DateTime($data['dateTo']))->setTimezone(new \DateTimeZone('UTC'));

        $trackLink = new TrackLink($data);
        $this->em->persist($trackLink);
        $this->em->flush();

        return $trackLink;
    }

    /**
     * @param $hash
     * @return mixed
     */
    public function getTrackLinkData(string $hash)
    {
        /** @var TrackLink $trackLink */
        $trackLink = $this->em->getRepository(TrackLink::class)->getByHash($hash);
        if (!$trackLink) {
            return null;
        }
        $coordinates = [];

        $device = $trackLink->getVehicle()->getDevice();
        if ($device) {
            $coordinates = $this->em->getRepository(TrackerHistory::class)->getCoordinatesByDeviceId(
                $device->getId(),
                $trackLink->getDateFrom()->format('c'),
                $trackLink->getDateTo()->format('c')
            );
        }

        $themeSetting = $trackLink->getCreatedBy()->getSettingByName(Setting::THEME_SETTING);

        $theme = !$themeSetting
            ? $this->em->getRepository(Theme::class)->findOneBy(['alias' => Theme::DEFAULT_THEME_ALIAS])
            : $this->em->getRepository(Theme::class)->find($themeSetting->getValue());

        return array_merge($trackLink->toArray(), ['coordinates' => $coordinates, 'theme' => $theme->toArray()]);
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @throws ValidationException
     */
    protected function validateCreateData(array $data, User $currentUser)
    {
        $errors = [];
        if ($data['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($data['vehicleId']);
            if (!$vehicle || $vehicle->getTeam()->getId() !== $currentUser->getTeamId()) {
                $errors['vehicleId'] = $this->translator->trans('validation.errors.field.wrong_value');
            }
        }

        if (!($data['dateFrom'] ?? null)) {
            $errors['dateFrom'] = $this->translator->trans('validation.errors.field.required');
        }

        if (!($data['dateTo'] ?? null)) {
            $errors['dateTo'] = $this->translator->trans('validation.errors.field.required');
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function sendLink(string $hash, array $data)
    {
        /** @var TrackLink $trackLink */
        $trackLink = $this->em->getRepository(TrackLink::class)->getByHash($hash);
        if (!$trackLink) {
            return null;
        }

        if ($data['phone'] ?? null) {
            foreach ($data['phone'] as $phone) {
                $this->smsService->send($phone, $trackLink->getMessage() . ' ' . $data['link'],
                    false, $trackLink->getCreatedBy()->getTeam()->getSmsName());
            }
        }

        if ($data['email'] ?? null) {
            $renderedEmail = $this->emailRender->render(
                'emails/track_link.html.twig',
                [
                    'client' => $trackLink->getCreatedBy()->getClient()->getName(),
                    'driver' => $trackLink->getDriverName(),
                    'regno' => $trackLink->getVehicle()->getRegNo(),
                    'message' => $trackLink->getMessage(),
                    'link' => $data['link'],
                    'emailName' => $trackLink->getCreatedBy()->getTeam()->getEmailName(),
                    'logoPath' => $trackLink->getCreatedBy()->getTeam()->getLogoPath()
                ]
            );

            $this->mailSender->sendEmail($data['email'], $renderedEmail,
                $trackLink->getCreatedBy()->getTeam()->getNotificationEmail()
            );
        }

        return $trackLink->toArray();
    }
}
