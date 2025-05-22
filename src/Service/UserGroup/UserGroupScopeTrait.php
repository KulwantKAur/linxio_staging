<?php

namespace App\Service\UserGroup;

use App\Entity\User;
use App\Entity\UserGroup;
use App\Events\UserGroup\UserGroupChangedScopeEvent;

trait UserGroupScopeTrait
{
    protected function fillScopeValues(UserGroup $userGroup, User $currentUser, $scope, $data)
    {
        $oldScope = $userGroup->getScope() ?? null;
        $vehiclesIdsByOldScope = $oldScope ? $userGroup->getVehiclesIdsByScope($oldScope) : [];
        $scopeValues = $data['scopeValues'] ?? null;
        $userGroup->setScope($scope);

        switch ($userGroup->getScope()) {
            case UserGroup::SCOPE_ALL:
                $userGroup->removeAllVehicles();
                $userGroup->removeAllDepots();
                $userGroup->removeAllVehicleGroups();
                $vehiclesIdsByTeam = $userGroup->getVehiclesIdsByScope(UserGroup::SCOPE_ALL);
                $vehiclesIdsToRemove = array_diff($vehiclesIdsByOldScope, $vehiclesIdsByTeam);
                $vehiclesIdsToAdd = array_diff($vehiclesIdsByTeam, $vehiclesIdsByOldScope);
                $this->eventDispatcher->dispatch(
                    new UserGroupChangedScopeEvent($userGroup, $currentUser, $vehiclesIdsToAdd, $vehiclesIdsToRemove),
                    UserGroupChangedScopeEvent::NAME
                );
                break;
            case UserGroup::SCOPE_VEHICLE:
                if ($scopeValues) {
                    $userGroup = $this->addVehicleToUserGroup($scopeValues, $userGroup, $currentUser);
                    $userGroup->removeAllDepots();
                    $userGroup->removeAllVehicleGroups();
                    $vehiclesIdsToRemove = array_diff($vehiclesIdsByOldScope, $scopeValues);
                    $vehiclesIdsToAdd = array_diff($scopeValues, $vehiclesIdsByOldScope);
                    $this->eventDispatcher->dispatch(
                        new UserGroupChangedScopeEvent(
                            $userGroup, $currentUser, $vehiclesIdsToAdd, $vehiclesIdsToRemove
                        ), UserGroupChangedScopeEvent::NAME
                    );
                }

                break;
            case UserGroup::SCOPE_DEPOT:
                if ($scopeValues) {
                    $userGroup = $this->addDepotToUserGroup($scopeValues, $userGroup, $currentUser);
                    $userGroup->removeAllVehicles();
                    $userGroup->removeAllVehicleGroups();
                    $vehiclesIdsByNewScopeValues = $userGroup->getVehiclesIdsByScope(UserGroup::SCOPE_DEPOT);
                    $vehiclesIdsToRemove = array_diff($vehiclesIdsByOldScope, $vehiclesIdsByNewScopeValues);
                    $vehiclesIdsToAdd = array_diff($vehiclesIdsByNewScopeValues, $vehiclesIdsByOldScope);
                    $this->eventDispatcher->dispatch(
                        new UserGroupChangedScopeEvent(
                            $userGroup, $currentUser, $vehiclesIdsToAdd, $vehiclesIdsToRemove
                        ), UserGroupChangedScopeEvent::NAME
                    );
                }

                break;
            case UserGroup::SCOPE_GROUP:
                if ($scopeValues) {
                    $userGroup = $this->addVehicleGroupToUserGroup($scopeValues, $userGroup, $currentUser);
                    $userGroup->removeAllVehicles();
                    $userGroup->removeAllDepots();
                    $vehiclesIdsByNewScopeValues = $userGroup->getVehiclesIdsByScope(UserGroup::SCOPE_GROUP);
                    $vehiclesIdsToRemove = array_diff($vehiclesIdsByOldScope, $vehiclesIdsByNewScopeValues);
                    $vehiclesIdsToAdd = array_diff($vehiclesIdsByNewScopeValues, $vehiclesIdsByOldScope);
                    $this->eventDispatcher->dispatch(
                        new UserGroupChangedScopeEvent(
                            $userGroup, $currentUser, $vehiclesIdsToAdd, $vehiclesIdsToRemove
                        ), UserGroupChangedScopeEvent::NAME
                    );
                }

                break;
        }

        return $userGroup;
    }

    protected function fillAreaScopeValues(UserGroup $userGroup, User $currentUser, $scope, $data)
    {
        $oldScope = $userGroup->getAreaScope() ?? null;
        $areasIdsByOldScope = $oldScope ? $userGroup->getAreasIdsByScope($oldScope) : [];
        $scopeValues = $data['areaScopeValues'] ?? null;
        $userGroup->setAreaScope($scope);

        switch ($userGroup->getAreaScope()) {
            case UserGroup::SCOPE_ALL:
                $userGroup->removeAllAreas();
                $userGroup->removeAllAreaGroups();
                $areasIdsByTeam = $userGroup->getAreasIdsByScope(UserGroup::SCOPE_ALL);
                $areasIdsToRemove = array_diff($areasIdsByOldScope, $areasIdsByTeam);
                $areasIdsToAdd = array_diff($areasIdsByTeam, $areasIdsByOldScope);
                $this->eventDispatcher->dispatch(
                    new UserGroupChangedScopeEvent($userGroup, $currentUser, $areasIdsToAdd, $areasIdsToRemove),
                    UserGroupChangedScopeEvent::NAME
                );
                break;
            case UserGroup::SCOPE_AREA:
                if ($scopeValues || empty($scopeValues)) {
                    $userGroup = $this->addAreaToUserGroup($scopeValues, $userGroup, $currentUser);
                    $userGroup->removeAllAreaGroups();
                    $areasIdsToRemove = array_diff($areasIdsByOldScope, $scopeValues);
                    $areasIdsToAdd = array_diff($scopeValues, $areasIdsByOldScope);
                    $this->eventDispatcher->dispatch(
                        new UserGroupChangedScopeEvent($userGroup, $currentUser, $areasIdsToAdd, $areasIdsToRemove),
                        UserGroupChangedScopeEvent::NAME
                    );
                }

                break;
            case UserGroup::SCOPE_AREA_GROUP:
                if ($scopeValues || empty($scopeValues)) {
                    $userGroup = $this->addAreaGroupToUserGroup($scopeValues, $userGroup, $currentUser);
                    $userGroup->removeAllAreas();
                    $areasIdsByNewScopeValues = $userGroup->getAreasIdsByScope(UserGroup::SCOPE_GROUP);
                    $areasIdsToRemove = array_diff($areasIdsByOldScope, $areasIdsByNewScopeValues);
                    $areasIdsToAdd = array_diff($areasIdsByNewScopeValues, $areasIdsByOldScope);
                    $this->eventDispatcher->dispatch(
                        new UserGroupChangedScopeEvent($userGroup, $currentUser, $areasIdsToAdd, $areasIdsToRemove),
                        UserGroupChangedScopeEvent::NAME
                    );
                }

                break;
        }

        return $userGroup;
    }
}