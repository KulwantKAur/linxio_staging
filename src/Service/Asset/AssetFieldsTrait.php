<?php

namespace App\Service\Asset;

use App\Entity\Asset;
use App\Entity\Depot;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VehicleGroup;
use App\Exceptions\ValidationException;
use App\Service\Client\ClientService;
use App\Service\VehicleGroup\VehicleGroupService;
use App\Util\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;

trait AssetFieldsTrait
{
    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateAssetFields(array $fields, User $currentUser)
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

        if (isset($fields['depotId']) && !is_null($fields['depotId'])) {
            $depot = $this->em->getRepository(Depot::class)->find($fields['depotId']);
            if (!$depot || !ClientService::checkTeamAccess($depot->getTeam(), $currentUser)) {
                $errors['depotId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if ($fields['groupId'] ?? null) {
            $group = $this->em->getRepository(VehicleGroup::class)->find($fields['groupId']);
            if (!$group || !VehicleGroupService::checkVehicleGroupAccess($group, $currentUser)) {
                $errors['groupId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $params
     * @return array
     */
    public static function handleStatusParams(array $params)
    {
        if (isset($params['status'])) {
            $params['status'] = $params['status'] === Asset::STATUS_ALL ? Asset::ALLOWED_STATUSES : $params['status'];
        } else {
            $params['status'] = Asset::STATUS_OK;
        }

        if (isset($params['showDeleted']) && StringHelper::stringToBool($params['showDeleted'])) {
            if (is_array($params['status'])) {
                $params['status'][] = Asset::STATUS_DELETED;
            } else {
                $status = $params['status'];
                $params['status'] = [$status, Asset::STATUS_DELETED];
            }
        } elseif (
            is_array($params['status']) && ($key = array_search(Asset::STATUS_DELETED, $params['status'])) !== false
        ) {
            unset($params['status'][$key]);
        } elseif (!is_array($params['status']) && $params['status'] === Asset::STATUS_DELETED) {
            $params['status'] = '';
        }

        return $params;
    }

    /**
     * @param $data
     * @param Asset $asset
     * @param User $currentUser
     * @return Asset
     */
    public function handleDepotGroupsParams($data, Asset $asset, User $currentUser)
    {
        $groups = isset($data['groupIds']) ? $this->em->getRepository(VehicleGroup::class)->findBy(['id' => $data['groupIds']]) : null;
        if (is_array($groups)) {
            $asset = $this->addAssetToGroups($asset, new ArrayCollection($groups), $currentUser);
        }

        $depot = isset($data['depotId']) && !is_null($data['depotId'])
            ? $this->em->getRepository(Depot::class)->find($data['depotId'])
            : null;
        if ($depot) {
            $asset->setDepot($depot);
        }

        return $asset;
    }
}