<?php

namespace App\Service\EventLog\Interfaces;

use App\Entity\User;

interface ReportBuilderInterface
{
    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @return array
     */
    public function getHeader(): array;

    /**
     * @param array $data
     * @param User $user
     * @param array $params
     * @return mixed
     */
    public function build(array $data, User $user, array $params = []);
}
