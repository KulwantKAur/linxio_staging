<?php

namespace App\Service\Sms\Plivo;

use App\Entity\Sms;
use App\Entity\User;
use App\Enums\SmsStatuses;
use App\Repository\SmsRepository;
use App\Service\Sms\Interfaces\SmsSendingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Plivo\Exceptions\PlivoRestException;
use Plivo\Resources\Message\MessageCreateResponse;
use Plivo\RestClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Plivo implements SmsSendingInterface
{
    private $client;
    private $numberFrom;
//    private $router;
    private $em;
    private $translator;
    private $apiUrl;

    public function __construct(
        $plivoAuthId,
        $plivoAuthToken,
        $plivoNumberFrom,
        string $apiUrl,
//        UrlGeneratorInterface $router,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
//        $this->router = $router;
        $this->client = new RestClient($plivoAuthId, $plivoAuthToken);
        $this->numberFrom = $plivoNumberFrom;
        $this->translator = $translator;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param string $to
     * @param string $text
     * @param bool $is2FA
     * @param $from
     * @return Sms
     * @throws PlivoRestException
     */
    public function send($to, string $text, $is2FA, $from = null)
    {
        try {
            $response = $this->client->messages->create(
                $from ?? $this->numberFrom,
                [$to],
                $text,
                [
                    'url' => $this->generateCallbackUrl(),
                    'trackable' => $is2FA
                ]
            );
        } catch (\Exception $exception) {
            throw new PlivoRestException($exception->getMessage(), $exception->getStatusCode());
            // @todo notify admin that something is broken
        }

        return $this->saveSms($to, $text, $response);
    }

    /**
     * @param $to
     * @param $text
     * @param MessageCreateResponse $response
     * @return Sms
     */
    public function saveSms($to, $text, $response): Sms
    {
        $uuid = $response->getMessageUuid()[0];

        $sms = new Sms();
        $sms->setPhoneFrom($this->numberFrom);
        $sms->setPhoneTo($to);
        $sms->setStatus(SmsStatuses::PENDING);
        $sms->setMessage($text);
        $sms->setMessageUuid($uuid);

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['phone' => $to]);

        if ($user) {
            $sms->setUser($user);
        }

        $this->em->persist($sms);
        $this->em->flush();

        return $sms;
    }

    /**
     * @param $data
     * @return Sms|SmsRepository|object
     */
    public function update($data)
    {
        $status = $data['Status'];
        $uuid = $data['MessageUUID'];
        $sms = $this->em->getRepository(Sms::class)->findOneBy([
            'messageUuid' => $uuid
        ]);

        if ($sms) {
            $sms->setStatus($status);
            $this->em->flush();
        }

        return $sms;
    }

    /**
     * @return string
     */
    private function generateCallbackUrl(): string
    {
//        $path = $this->router->generate(
//            'sms_status_callback',
//            [],
//            UrlGeneratorInterface::ABSOLUTE_PATH
//        );

        $path = '/api/sms/inbound/status-callback';

        return $this->apiUrl . $path;
    }

    public function setNumberFrom($from)
    {
        $this->numberFrom = $from;

        return $this;
    }
}