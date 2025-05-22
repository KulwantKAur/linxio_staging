<?php

namespace App\Command;

use App\Entity\TokenBlacklist;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:remove-expired-tokens')]
class RemoveExpiredTokensCommand extends Command
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Remove all expired blocked tokens');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->em->createQueryBuilder()
            ->delete(TokenBlacklist::class, 't')
            ->andWhere('t.expiredAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();

        $output->writeln('Expired tokens successfully deleted');

        return 0;
    }
}