<?php

namespace App\Service\Depot;

use App\Entity\Depot;
use App\Entity\Team;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;
use App\Util\StringHelper;

trait DepotServiceFieldsTrait
{
    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateVehicleFields(array $fields, User $currentUser)
    {
        $errors = [];
        if ($currentUser->isInAdminTeam()) {
            if (!isset($fields['teamId']) || !$fields['teamId']) {
                $errors['teamId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
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

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}