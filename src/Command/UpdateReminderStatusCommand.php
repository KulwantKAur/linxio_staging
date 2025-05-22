<?php

namespace App\Command;

use App\Entity\DocumentRecord;
use App\Entity\Reminder;
use App\Service\ServiceRecord\ServiceRecordService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:reminder:update-statuses')]
class UpdateReminderStatusCommand extends Command
{
    private const BATCH_SIZE = 100;

    private $em;
    /** @var \App\Repository\ReminderRepository */
    private $reminderRepository;

    private $serviceRecordService;

    protected function configure(): void
    {
        $this->setDescription('Update reminder status');
    }

    /**
     * UpdateDocumentRecordsStatusCommand constructor.
     * @param EntityManager $em
     * @param ServiceRecordService $serviceRecordService
     */
    public function __construct(EntityManager $em, ServiceRecordService $serviceRecordService)
    {
        $this->em = $em;
        $this->serviceRecordService = $serviceRecordService;
        $this->reminderRepository = $em->getRepository(Reminder::class);

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
        foreach ($this->reminderRepository->getActiveReminders(true) as $reminder) {
            /** @var Reminder $reminder */
            $reminder = $this->serviceRecordService->updateStatus($reminder, $reminder->getStatus());

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