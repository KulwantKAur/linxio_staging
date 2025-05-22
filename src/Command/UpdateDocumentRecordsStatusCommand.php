<?php

namespace App\Command;

use App\Entity\DocumentRecord;
use App\Service\Vehicle\DocumentRecordService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:document:update-statuses')]
class UpdateDocumentRecordsStatusCommand extends Command
{
    private const BATCH_SIZE = 1000;

    private $em;
    private $documentRecordRepo;

    private $documentRecordService;

    protected function configure(): void
    {
        $this->setDescription('Update document records status');
    }

    /**
     * UpdateDocumentRecordsStatusCommand constructor.
     * @param EntityManager $em
     * @param DocumentRecordService $documentRecordService
     */
    public function __construct(EntityManager $em, DocumentRecordService $documentRecordService)
    {
        $this->em = $em;
        $this->documentRecordService = $documentRecordService;
        $this->documentRecordRepo = $em->getRepository(DocumentRecord::class);

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $counter = 0;
        foreach ($this->documentRecordRepo->getActiveDocumentRecords(true) as $documentRecord) {
            $newStatus = $this->documentRecordService->calculateStatus($documentRecord, $documentRecord->getStatus());
            if ($newStatus !== $documentRecord->getStatus()) {
                /** @var DocumentRecord $documentRecord */
                $documentRecord->setStatus($newStatus);
                $documentRecord->getDocument()->setUpdatedAt(new \DateTime());
            }

            if (($counter % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
            ++$counter;
        }

        $this->em->flush();

        return 0;
    }
}