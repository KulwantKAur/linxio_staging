<?php

namespace App\Service\UserGroup;

use App\Entity\Area;
use App\Entity\AreaGroup;
use App\Entity\Depot;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Events\UserGroup\UserAddedToUserGroupEvent;
use App\Events\UserGroup\UserRemovedFromUserGroupEvent;
use App\Service\Client\ClientService;

trait UserGroupEntityTrait
{
    public function addUserToUserGroup(array $userIds, UserGroup $userGroup)
    {
        $userGroupUsersIds = $userGroup->getUserIds();
        $idsToDelete = array_diff($userGroupUsersIds, $userIds);

        foreach ($idsToDelete as $idToDelete) {
            $user = $this->em->getRepository(User::class)->find($idToDelete);
            $user->removeFromGroup($userGroup);
            $user->setUpdatedAt(new \DateTime());
            $userGroup->removeUser($user);
            $this->eventDispatcher
                ->dispatch(
                    new UserRemovedFromUserGroupEvent($user, $userGroup),
                    UserRemovedFromUserGroupEvent::NAME
                );
        }

        foreach ($userIds as $userId) {
            /** @var User $user */
            $user = $this->em->getRepository(User::class)->find($userId);

            if ($user->getTeamId() === $userGroup->getTeam()->getId()) {
                $user->addToGroup($userGroup);
                $user->setUpdatedAt(new \DateTime());
                $userGroup->addUser($user);
                $this->eventDispatcher
                    ->dispatch(new UserAddedToUserGroupEvent($user, $userGroup), UserAddedToUserGroupEvent::NAME);
            }
        }

        return $userGroup;
    }

    public function addVehicleToUserGroup(array $vehicleIds, UserGroup $userGroup, User $currentUser)
    {
        $idsToDelete = array_diff($userGroup->getVehicleIds(), $vehicleIds);
        foreach ($idsToDelete as $idToDelete) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($idToDelete);
            if (ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser)) {
                $vehicle->removeFromUserGroup($userGroup);
                $vehicle->setUpdatedAt(new \DateTime());
                $userGroup->removeVehicle($vehicle);
            }
        }

        foreach ($vehicleIds as $vehicleId) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
            if (ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser)) {
                $vehicle->addToUserGroup($userGroup);
                $vehicle->setUpdatedAt(new \DateTime());
                $userGroup->addVehicle($vehicle);
            }
        }

        return $userGroup;
    }

    public function addAreaToUserGroup(array $areaIds, UserGroup $userGroup, User $currentUser)
    {
        $idsToDelete = array_diff($userGroup->getAreaIds(), $areaIds);
        foreach ($idsToDelete as $idToDelete) {
            $area = $this->em->getRepository(Area::class)->find($idToDelete);
            if ($area && ClientService::checkTeamAccess($area->getTeam(), $currentUser)) {
                $area->removeFromUserGroup($userGroup);
                $area->setUpdatedAt(new \DateTime());
                $userGroup->removeArea($area);
            }
        }

        foreach ($areaIds as $areaId) {
            $area = $this->em->getRepository(Area::class)->find($areaId);
            if ($area && ClientService::checkTeamAccess($area->getTeam(), $currentUser)) {
                $area->addToUserGroup($userGroup);
                $area->setUpdatedAt(new \DateTime());
                $userGroup->addArea($area);
            }
        }

        return $userGroup;
    }

    public function addVehicleGroupToUserGroup(array $vehicleGroupIds, UserGroup $userGroup, User $currentUser)
    {
        $idsToDelete = array_diff($userGroup->getVehicleGroupIds(), $vehicleGroupIds);
        foreach ($idsToDelete as $idToDelete) {
            $vehicleGroup = $this->em->getRepository(VehicleGroup::class)->find($idToDelete);
            if (ClientService::checkTeamAccess($vehicleGroup->getTeam(), $currentUser)) {
                $vehicleGroup->removeFromUserGroup($userGroup);
                $userGroup->removeVehicleGroup($vehicleGroup);
            }
        }

        foreach ($vehicleGroupIds as $vehicleGroupId) {
            $vehicleGroup = $this->em->getRepository(VehicleGroup::class)->find($vehicleGroupId);
            if (ClientService::checkTeamAccess($vehicleGroup->getTeam(), $currentUser)) {
                $vehicleGroup->addToUserGroup($userGroup);
                $userGroup->addVehicleGroup($vehicleGroup);
            }
        }

        return $userGroup;
    }

    public function addAreaGroupToUserGroup(array $areaGroupIds, UserGroup $userGroup, User $currentUser)
    {
        $idsToDelete = array_diff($userGroup->getAreaGroupIds(), $areaGroupIds);
        foreach ($idsToDelete as $idToDelete) {
            $areaGroup = $this->em->getRepository(AreaGroup::class)->find($idToDelete);
            if (ClientService::checkTeamAccess($areaGroup->getTeam(), $currentUser)) {
                $areaGroup->removeFromUserGroup($userGroup);
                $userGroup->removeAreaGroup($areaGroup);
            }
        }

        foreach ($areaGroupIds as $areaGroupId) {
            $areaGroup = $this->em->getRepository(AreaGroup::class)->find($areaGroupId);
            if (ClientService::checkTeamAccess($areaGroup->getTeam(), $currentUser)) {
                $areaGroup->addToUserGroup($userGroup);
                $userGroup->addAreaGroup($areaGroup);
            }
        }

        return $userGroup;
    }

    public function addDepotToUserGroup(array $depotIds, UserGroup $userGroup, User $currentUser)
    {
        $oldDepotIds = $userGroup->getDepotIds();
        $idsToDelete = array_diff($userGroup->getDepotIds(), $depotIds);

        foreach ($idsToDelete as $idToDelete) {
            $depot = $this->em->getRepository(Depot::class)->find($idToDelete);

            if (ClientService::checkTeamAccess($depot->getTeam(), $currentUser)) {
                $depot->removeFromUserGroup($userGroup);
                $depot->setUpdatedAt(new \DateTime());
                $userGroup->removeDepot($depot);
            }
        }

        foreach ($depotIds as $depotId) {
            $depot = $this->em->getRepository(Depot::class)->find($depotId);

            if (ClientService::checkTeamAccess($depot->getTeam(), $currentUser) && !in_array($depotId, $oldDepotIds)) {
                $depot->addToUserGroup($userGroup);
                $depot->setUpdatedAt(new \DateTime());
                $userGroup->addDepot($depot);
            }
        }

        return $userGroup;
    }
}