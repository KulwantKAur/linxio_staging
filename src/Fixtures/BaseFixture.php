<?php

namespace App\Fixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManager;

abstract class BaseFixture extends Fixture
{
    /**
     * @param $em
     * @return EntityManager
     */
    protected function prepareEntityManager($em)
    {
        if (!$em->isOpen()) {
            $em = $em->create($em->getConnection(), $em->getConfiguration());
        }
        return $em;
    }
}