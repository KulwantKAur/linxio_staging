<?php

namespace App\EventListener\ScheduledReport;

use App\Entity\Role;
use App\Entity\ScheduledReport;
use App\Entity\ScheduledReportRecipients;
use App\Entity\User;
use App\Entity\UserGroup;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Container\ContainerInterface;

class ScheduledReportEntityListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postLoad(ScheduledReport $scheduledReport, LifecycleEventArgs $args)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $recipient = $scheduledReport->getRecipient();
        $emails = $recipient->getEmails();
        $data = [['type' => ScheduledReportRecipients::TYPE_EMAIL, 'value' => $emails]];
        switch ($recipient->getType()) {
            case ScheduledReportRecipients::TYPE_USER:
                $users = $em->getRepository(User::class)
                    ->findBy(['id' => $recipient->getValue(), 'team' => $scheduledReport->getCreatedBy()->getTeam()]);
                $data[] = [
                    'type' => $recipient->getType(),
                    'value' => array_map(function ($user) {
                        return $user->toArray(['name', 'surname']);
                    }, $users)
                ];
                break;
            case ScheduledReportRecipients::TYPE_USER_GROUP:
                $userGroups = $em->getRepository(UserGroup::class)
                    ->findBy(['id' => $recipient->getValue(), 'team' => $scheduledReport->getCreatedBy()->getTeam()]);
                $data[] = [
                    'type' => $recipient->getType(),
                    'value' => array_map(function ($userGroup) {
                        return $userGroup->toArray(['name']);
                    }, $userGroups)
                ];
                break;
            case ScheduledReportRecipients::TYPE_ROLE:
                $roles = $em->getRepository(Role::class)->findBy(['id' => $recipient->getValue()]);
                $data[] = [
                    'type' => $recipient->getType(),
                    'value' => array_map(function ($role) {
                        return $role->toArray();
                    }, $roles)
                ];
                break;
        }
        $scheduledReport->setRecipientData($data);
    }
}