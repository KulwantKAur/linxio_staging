<?php

namespace App\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'db:procedures:insert')]
class InsertPgProceduresCommand extends Command
{
    private $em;
    private $params;

    /**
     * InsertPgProceduresCommand constructor.
     * @param EntityManager $entityManager
     * @param ParameterBagInterface $params
     */
    public function __construct(EntityManager $entityManager, ParameterBagInterface $params)
    {
        $this->em = $entityManager;
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Insert procedures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $proceduresPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'procedures';

        $finder = new Finder();
        $finder->files()->in($proceduresPath);
        $connection = $this->em->getConnection();

        if ($connection->getDatabasePlatform()->getName() !== 'postgresql') {
            throw new \Exception('Procedures can only be executed safely on \'postgresql\'.');
        }

        foreach ($finder as $file) {
            if (substr($file->getRelativePathname(), -4) === '.php') {
                $className = substr($file->getRelativePathname(), 0, -4);

                if (substr($className, -9) !== 'Interface') {
                    $fullClassName = 'App\Resources\procedures\\' . $className;
                    $output->write(str_pad(sprintf('Processing: %s', $fullClassName), 80, ' '));
                    try {
                        $connection->prepare(call_user_func($fullClassName . '::up'))->execute();
                        $output->write(' <info>(+)</info> ');
                        $output->writeln('');
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }

        return 0;
    }
}