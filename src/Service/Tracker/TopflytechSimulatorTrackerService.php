<?php

namespace App\Service\Tracker;

use App\Service\Tracker\Parser\Topflytech\TcpDecoder;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopflytechSimulatorTrackerService extends SimulatorTrackerService
{
    /**
     * TeltonikaSimulatorTrackerService constructor.
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $modelName, \DateTimeInterface $createdAt): array
    {
        $decoder = new TcpDecoder();

        return $decoder->encodePayloadWithNewDateTime($payload, $modelName, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getAuthPayload(string $imei): string
    {
        return '';
    }
}