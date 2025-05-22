<?php

namespace App\Command\FuelCard;

use App\Entity\FuelCard\FuelCard;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fuelcard:remove-expired-files')]
class RemoveExpiredFuelCardFilesCommand extends Command
{
    private $em;

    /** @var \App\Repository\FuelCard\FuelCardRepository */
    private $fuelCardRepository;

    protected function configure(): void
    {
        $this->setDescription('Remove expired fuel card files');
    }

    /**
     * RemoveFuelCardFilesCommand constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->fuelCardRepository = $em->getRepository(FuelCard::class);

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
        $this->fuelCardRepository->deleteExpiredFiles();

        $output->writeln('Expired files successfully deleted');

        return 0;
    }
}