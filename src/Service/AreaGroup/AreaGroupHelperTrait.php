<?php

namespace App\Service\AreaGroup;

use App\Entity\AreaGroup;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;
use App\Util\StringHelper;

trait AreaGroupHelperTrait
{
    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateAreaFields(array $fields, User $currentUser, $actionType)
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
            $areaGroup = $this->em->getRepository(AreaGroup::class)->findBy(
                [
                    'team' => $team,
                    'name' => $fields['name']
                ]
            );
            if ($areaGroup && $actionType === self::ACTION_CREATE) {
                $errors['areaGroup'] = ['required' => $this->translator->trans('entities.already_exist')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    private function handleUserGroupParams(array $params, User $user)
    {
        if ($user->needToCheckUserGroup()) {
            $params['id'] = $this->em->getRepository(UserGroup::class)->getUserAreaGroupsIdFromUserGroup($user) ?? [];
        }

        return $params;
    }
}