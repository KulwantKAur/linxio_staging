<?php

namespace App\Service\UserGroup;

use App\Entity\Notification\Event;
use App\Entity\Permission;
use App\Entity\PlanRolePermission;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;

trait UserGroupFieldsTrait
{
    public function prepareCreateParams(array $params)
    {
        if (isset($params['permissions'])) {
            $params['permissions'] = $this->em->getRepository(Permission::class)->findBy(['name' => $params['permissions']]);
        }

        return $params;
    }

    public function prepareEditParams(array $params)
    {
        if (isset($params['permissions'])) {
            $params['permissions'] = $this->em->getRepository(Permission::class)->getByNameIndexById($params['permissions']);
        }

        return $params;
    }

    private function validateUserFields(array $fields, User $currentUser, $actionType)
    {
        $errors = [];
        if (!($fields['name'] ?? null)) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if ($currentUser->isInAdminTeam()) {
            if (!($fields['teamId'] ?? null)) {
                $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            if ($fields['teamId'] ?? null) {
                $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
                if (!$team) {
                    $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
                }
                if ($team && !ClientService::checkTeamAccess($team, $currentUser)) {
                    $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
                }
            }
        }

        if (isset($team) && $team && $fields['name'] ?? null) {
            $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
            $userGroup = $this->em->getRepository(UserGroup::class)->findBy(
                [
                    'team' => $team,
                    'name' => $fields['name']
                ]
            );
            if ($userGroup && $actionType === self::ACTION_CREATE) {
                $errors['userGroup'] = ['required' => $this->translator->trans('entities.already_exist')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    private function validateUserGroupEditFields(array $fields, User $currentUser)
    {
        $errors = [];

        if ($currentUser->isInAdminTeam()) {
            if ($fields['teamId'] ?? null) {
                $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
                if (!$team) {
                    $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
                }
                if ($team && !ClientService::checkTeamAccess($team, $currentUser)) {
                    $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
                }
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}