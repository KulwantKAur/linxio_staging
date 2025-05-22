<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Area;
use App\Entity\Route;
use App\Entity\RouteFinishArea;
use App\Entity\RouteStartArea;
use App\Service\Redis\MemoryDbService;
use App\Service\Route\RouteAreaConsumer;
use App\Service\Route\RouteService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:route:area')]
class RecalculateRouteArea extends Command
{
//    use RedisLockTrait;
    use CommandLoggerTrait;

    private $params;
    private $memoryDb;
    private EntityManager $em;

    public function __construct(
        ParameterBagInterface $params,
        MemoryDbService $memoryDbService,
        private readonly RouteService $routeService,
        EntityManager $em
    ) {
        $this->params = $params;
        $this->memoryDb = $memoryDbService;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('startId', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('finishId', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('queue', null, InputOption::VALUE_OPTIONAL, '');
        $this->setDescription('RecalculateRouteArea');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startId = $input->getOption('startId');
        $finishId = $input->getOption('finishId');
        $queue = $input->getOption('queue') ?? false;
        for ($i = $startId; $i <= $finishId; $i++) {
            if ($queue) {
                $this->routeService->triggerRouteAreaProducer([
                    [
                        'routeId' => $i,
                        'deviceId' => rand(1, RouteAreaConsumer::QUEUES_NUMBER)
                    ]
                ]);
            } else {
                $route = $this->em->getRepository(Route::class)->find($i);

                if (!$route) {
                    continue;
                }

                $area = $this->em->getRepository(Area::class)->count([
                    'team' => $route->getDevice()->getTeam(),
                    'status' => Area::STATUS_ACTIVE
                ]);

                if (!$area) {
                    continue;
                }

                if ($route->getPointStart()?->getLng() && $route->getPointStart()?->getLat()) {
                    $startAreas = $this->em->getRepository(Area::class)->findByPoint(
                        $route->getPointStart()->getLng() . ' ' . $route->getPointStart()->getLat(),
                        $route->getDevice()->getTeam()
                    );
                }

                if ($route->getPointFinish()?->getLng() && $route->getPointFinish()?->getLat()) {
                    $finishAreas = $this->em->getRepository(Area::class)->findByPoint(
                        $route->getPointFinish()->getLng() . ' ' . $route->getPointFinish()->getLat(),
                        $route->getDevice()->getTeam()
                    );
                }
                $output->writeln('route id: ' . $route->getId() . 'area count: ' . count($startAreas ?? []) . ' ' . count($finishAreas ?? []));

                if ($startAreas ?? null) {
                    foreach ($startAreas as $startArea) {
                        if (!$route->checkStartArea($startArea)) {
                            $routeStartArea = new RouteStartArea(['area' => $startArea, 'route' => $route]);
                            $this->em->persist($routeStartArea);
                        }
                    }
                } else {
                    $route->removeStartAreas();
                }

                if ($finishAreas ?? null) {
                    foreach ($finishAreas as $finishArea) {
                        if (!$route->checkFinishArea($finishArea)) {
                            $routeFinishArea = new RouteFinishArea(['area' => $finishArea, 'route' => $route]);
                            $this->em->persist($routeFinishArea);
                        }
                    }
                } else {
                    $route->removeFinishAreas();
                }

                $this->em->flush();
            }
            $this->em->clear();
        }

        return 0;
    }
}
