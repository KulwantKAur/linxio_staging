<?php

namespace App\Command;

use App\Entity\ChatHistory;
use App\Service\Chat\ChatServiceInterface;
use App\Service\Redis\MemoryDbService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:chat:update-data')]
class UpdateChatDataCommand extends Command
{
    private const BATCH_SIZE = 50;
    public const ALLOWED_ATTACHMENTS_DAYS = 30;

    private EntityManager $em;
    private MemoryDbService $memoryDb;
    private ChatServiceInterface $chatService;

    protected function configure(): void
    {
        $this->setDescription('Update chat data');
    }

    /**
     * @param EntityManager $em
     * @param MemoryDbService $memoryDb
     * @param ChatServiceInterface $chatService
     */
    public function __construct(EntityManager $em, MemoryDbService $memoryDb, ChatServiceInterface $chatService)
    {
        $this->em = $em;
        $this->memoryDb = $memoryDb;
        $this->chatService = $chatService;
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
        $chatMessagesQuery = $this->em->getRepository(ChatHistory::class)->getChatMessagesWithOldAttachmentsQuery();
        $chatMessagesCount = $this->em->getRepository(ChatHistory::class)->getChatMessagesWithOldAttachmentsCount();
//        $chatMessagesIds = $this->em->getRepository(ChatHistory::class)->getChatMessagesWithOldAttachmentsIds();
        $progressBar = new ProgressBar($output, $chatMessagesCount);
        $progressBar->start();

        /** @var ChatHistory $chatMessage */
        foreach ($chatMessagesQuery->toIterable() as $chatMessage) {
            try {
                $this->chatService->deleteChatMessageAttachmentByJob($chatMessage);

                if (($counter % self::BATCH_SIZE) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }

                ++$counter;
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln('Chat history: ' . $chatMessage->getId());
                $output->writeln($e->getTraceAsString());
            }

            $progressBar->advance();

        }

        $progressBar->finish();
        $this->em->flush();
        $this->em->clear();
        $output->writeln(PHP_EOL . 'Chat data successfully updated!');

        return 0;
    }
}