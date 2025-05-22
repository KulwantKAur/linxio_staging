<?php

namespace App\Service\Area;

use App\Entity\Area;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;
use App\Util\StringHelper;

trait AreaServiceHelperTrait
{
    public function handleUserGroupParams(array $params, User $user)
    {
        if ($user->needToCheckUserGroup()) {
            $areaIds = $this->em->getRepository(UserGroup::class)->getUserAreasIdFromUserGroup($user);
            if (isset($params['id'])) {
                if (is_array($params['id'])) {
                    $params['id'] = array_intersect($areaIds, $params['id']);
                } elseif (!in_array($params['id'], $areaIds)) {
                    $params['id'] = null;
                }
            } else {
                $params['id'] = $areaIds;
            }
        }

        return $params;
    }

    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateAreaFields(array $fields, User $currentUser)
    {
        $errors = [];
        if ($currentUser->isInAdminTeam()) {
            if ($fields['teamId'] ?? null) {
                $team = $this->em->getRepository(Team::class)->find($fields['teamId']);
                if (!$team || !ClientService::checkTeamAccess($team, $currentUser)) {
                    $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
                }
            }
        }

        if (!($fields['name'] ?? null)) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (!($fields['coordinates'] ?? null)) {
            $errors['coordinates'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}