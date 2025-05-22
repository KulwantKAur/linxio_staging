<?php

namespace App\Service\Notification;

use App\Entity\Area;
use App\Entity\AreaHistory;
use App\Entity\Asset;
use App\Entity\Device;
use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use \App\Entity\Notification\ScopeType;
use App\Entity\Reminder;
use App\Entity\Route;
use App\Entity\ServiceRecord;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

class ScopeService
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $notifications
     * @param $entity
     * @return array
     */
    public function filterNotifications($notifications, $entity, $context = []): array
    {
        $filtered = [];

        foreach ($notifications as $notification) {
            if ($this->checkScopes($notification, $entity, $context)) {
                $filtered[] = $notification;
            }
        }

        return $filtered;
    }

    /**
     * @param Notification $notification
     * @param $entity
     * @return bool
     */
    protected function checkScopes(Notification $notification, $entity, $context = []): bool
    {
        $scopes = $notification->getScopes();

        if (0 === $scopes->count()) {
            return false;
        }

        $checkScope = true;
        foreach ($scopes as $scope) {
            $handler = $this->getScopeHandler($scope->getType());

            $checkScope = $checkScope && $handler($entity, $scope->getValue(), $notification, $context);
        }

        return $checkScope;
    }

    /**
     * @param ScopeType $type
     * @return \Closure
     */
    private function getScopeHandler(ScopeType $type): \Closure
    {
        return [
                ScopeType::USER => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_USER => static function (User $entity, ?array $args = null) {
                        return in_array($entity->getId(), $args);
                    },
                    ScopeType::SUBTYPE_ROLE => static function (User $entity, ?array $args = null) {
                        return in_array($entity->getRole()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_TEAM => static function (User $entity, ?array $args = null) {
                        return in_array($entity->getTeam()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_USER_GROUPS => static function (User $entity, ?array $args = null) {
                        if (!$args) {
                            return false;
                        }
                        return (bool)count(array_intersect($entity->getGroupsId(), $args));
                    },
                ],
                ScopeType::VEHICLE => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_VEHICLE => static function ($entity, ?array $args = null) {
                        switch (ClassUtils::getClass($entity)) {
                            case Event::ENTITY_TYPE_ROUTE:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY:
                            case Event::ENTITY_TYPE_IDLING:
                            case Event::ENTITY_TYPE_SPEEDING:
                            case Event::ENTITY_TYPE_VEHICLE_ODOMETER:
                            case Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR:
                            case Event::ENTITY_TYPE_AREA_HISTORY:
                            case Event::ENTITY_TYPE_REMINDER:
                            case Event::ENTITY_TYPE_DOCUMENT:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY_IO:
                                return $entity->getVehicle() && in_array($entity->getVehicle()->getId(), $args);

                            case Event::ENTITY_TYPE_VEHICLE:
                                /** @var Vehicle $entity */
                                return in_array($entity->getId(), $args);
                            default:
                                return false;
                        }
                    },
                    ScopeType::SUBTYPE_DEPOT => static function ($entity, ?array $args = null) {
                        switch (ClassUtils::getClass($entity)) {
                            case Event::ENTITY_TYPE_ROUTE:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY:
                            case Event::ENTITY_TYPE_IDLING:
                            case Event::ENTITY_TYPE_SPEEDING:
                            case Event::ENTITY_TYPE_VEHICLE_ODOMETER:
                            case Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR:
                            case Event::ENTITY_TYPE_REMINDER:
                            case Event::ENTITY_TYPE_DOCUMENT:
                            case Event::ENTITY_TYPE_AREA_HISTORY:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY_IO:
                                return $entity->getVehicle() && $entity->getVehicle()->getDepot()
                                    && in_array($entity->getVehicle()->getDepot()->getId(), $args);

                            case Event::ENTITY_TYPE_VEHICLE:
                                /** @var Vehicle $entity */
                                return $entity->getDepot() && in_array($entity->getDepot()->getId(), $args);
                            default:
                                return false;
                        }
                    },
                    ScopeType::SUBTYPE_GROUP => static function ($entity, ?array $args = null) {
                        switch (ClassUtils::getClass($entity)) {
                            case Event::ENTITY_TYPE_ROUTE:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY:
                            case Event::ENTITY_TYPE_IDLING:
                            case Event::ENTITY_TYPE_SPEEDING:
                            case Event::ENTITY_TYPE_VEHICLE_ODOMETER:
                            case Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR:
                            case Event::ENTITY_TYPE_REMINDER:
                            case Event::ENTITY_TYPE_DOCUMENT:
                            case Event::ENTITY_TYPE_AREA_HISTORY:
                            case Event::ENTITY_TYPE_TRACKER_HISTORY_IO:
                                if ($entity->getVehicle() && $entity->getVehicle()->getGroups()) {
                                    $intersectValues = array_intersect(
                                        $args,
                                        $entity->getVehicle()->getGroups()->map(
                                            static function (VehicleGroup $g) {
                                                return $g->getId();
                                            }
                                        )->toArray()
                                    );

                                    return (bool)count($intersectValues);
                                } else {
                                    return false;
                                }

                            case Event::ENTITY_TYPE_VEHICLE:
                                /** @var Vehicle $entity */
                                if ($entity->getGroups()) {
                                    $intersectValues = array_intersect(
                                        $args,
                                        $entity->getGroups()->map(
                                            static function (VehicleGroup $g) {
                                                return $g->getId();
                                            }
                                        )->toArray()
                                    );

                                    return (bool)count($intersectValues);
                                } else {
                                    return false;
                                }
                            default:
                                return false;
                        }
                    },
                    ScopeType::SUBTYPE_TEAM => static function ($entity, ?array $args = null) {
                        switch (ClassUtils::getClass($entity)) {
                            case Event::ENTITY_TYPE_ROUTE:
                                /** @var Route $entity */
                                return $entity->getVehicle()
                                    && in_array($entity->getVehicle()->getTeam()->getId(), $args);

                            case Event::ENTITY_TYPE_VEHICLE:
                                /** @var Vehicle $entity */
                                return in_array($entity->getTeam()->getId(), $args);
                            default:
                                return false;
                        }
                    },

                    ScopeType::SUBTYPE_SENSOR => static function (TrackerHistorySensor $entity, ?array $args = null) {
                        return in_array($entity->getSensor()->getId(), $args);
                    },
                ],
                ScopeType::TEAM => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_TEAM => static function (Team $entity, ?array $args = null) {
                        return in_array($entity->getId(), $args);
                    },
                    ScopeType::SUBTYPE_TEAM_TYPE => static function (Team $entity, ?array $args = null) {
                        return in_array($entity->getType(), $args);
                    },
                ],
                ScopeType::DEVICE => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_DEVICE => static function (Device $entity, ?array $args = null) {
                        return in_array($entity->getId(), $args);
                    },
                ],
                ScopeType::AREA_HISTORY => [
                    ScopeType::SUBTYPE_ANY => function (
                        AreaHistory $entity,
                        $args = null,
                        ?Notification $ntf = null,
                        array $context = []
                    ) {
                        return $this->checkAreaTriggerType($ntf, [], $context, ScopeType::SUBTYPE_ANY);
                    },
                    ScopeType::SUBTYPE_AREA => function (
                        AreaHistory $entity,
                        ?array $args = null,
                        ?Notification $ntf = null,
                        array $context = []
                    ) {
                        return $this->checkAreaTriggerType($ntf, $args, $context, ScopeType::SUBTYPE_AREA, $entity);
                    },
                    ScopeType::SUBTYPE_AREAS_GROUP => function (
                        AreaHistory $entity,
                        ?array $args = null,
                        ?Notification $ntf = null,
                        array $context = []
                    ) {
                        if ($entity->getArea() && $entity->getArea()->getGroups()) {
                            return $this
                                ->checkAreaTriggerType($ntf, $args, $context, ScopeType::SUBTYPE_AREAS_GROUP, $entity);
                        } else {
                            return false;
                        }
                    },
                    ScopeType::SUBTYPE_VEHICLE => static function (AreaHistory $entity, ?array $args = null) {
                        return $entity->getVehicle() && in_array($entity->getVehicle()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_DEPOT => static function (AreaHistory $entity, ?array $args = null) {
                        return $entity->getVehicle() && $entity->getVehicle()->getDepot()
                            && in_array($entity->getVehicle()->getDepot()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_GROUP => static function (AreaHistory $entity, ?array $args = null) {
                        if ($entity->getVehicle() && $entity->getVehicle()->getGroups()) {
                            $intersectValues = array_intersect(
                                $args,
                                $entity->getVehicle()->getGroups()->map(
                                    static function (VehicleGroup $g) {
                                        return $g->getId();
                                    }
                                )->toArray()
                            );

                            return (bool)count($intersectValues);
                        } else {
                            return false;
                        }
                    },
                ],
                ScopeType::TRACKER_HISTORY => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_DEVICE => static function (TrackerHistory $entity, ?array $args = null) {
                        return $entity->getDevice() && in_array($entity->getDevice()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_VEHICLE => static function (TrackerHistory $entity, ?array $args = null) {
                        return $entity->getVehicle() && in_array($entity->getVehicle()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_DEPOT => static function (TrackerHistory $entity, ?array $args = null) {
                        return $entity->getVehicle() && $entity->getVehicle()->getDepot()
                            && in_array($entity->getVehicle()->getDepot()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_GROUP => static function (TrackerHistory $entity, ?array $args = null) {
                        if ($entity->getVehicle() && $entity->getVehicle()->getGroups()) {
                            $intersectValues = array_intersect(
                                $args,
                                $entity->getVehicle()->getGroups()->map(
                                    static function (VehicleGroup $g) {
                                        return $g->getId();
                                    }
                                )->toArray()
                            );

                            return (bool)count($intersectValues);
                        } else {
                            return false;
                        }
                    },
                ],
                ScopeType::DOCUMENT => [
                    /** TODO: Change the check for drivers or create a separate event for them */
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_VEHICLE => static function (Document $entity, ?array $args = null) {
                        return $entity->isVehicleDocument() && in_array($entity->getVehicle()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_DEPOT => static function (Document $entity, ?array $args = null) {
                        return $entity->isVehicleDocument() && $entity->getVehicle() && $entity->getVehicle()->getDepot()
                            && in_array($entity->getVehicle()->getDepot()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_GROUP => static function (Document $entity, ?array $args = null) {
                        if ($entity->isVehicleDocument()
                            && $entity->getVehicle()
                            && $entity->getVehicle()->getGroups()
                        ) {
                            $intersectValues = array_intersect(
                                $args,
                                $entity->getVehicle()->getGroups()->map(
                                    static function (VehicleGroup $g) {
                                        return $g->getId();
                                    }
                                )->toArray()
                            );

                            return (bool)count($intersectValues);
                        } else {
                            return false;
                        }
                    },
                    ScopeType::SUBTYPE_DRIVER => static function (Document $entity, ?array $args = null) {
                        if ($entity->isVehicleDocument() && $entity->getVehicle() && $entity->getVehicle()->getDriver()) {
                            return in_array($entity->getVehicle()->getDriver()->getId(), $args);
                        } elseif ($entity->isDriverDocument() && $entity->getDriver()) {
                            return in_array($entity->getDriver()->getId(), $args);
                        } else {
                            return false;
                        }
                    },
                ],
                ScopeType::DOCUMENT_RECORD => [
                    /** TODO: Change the check for drivers or create a separate event for them */
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_VEHICLE => static function (DocumentRecord $entity, ?array $args = null) {
                        return $entity->getDocument() && $entity->getDocument()->isVehicleDocument() && $entity->getDocument()->getVehicle()
                            && in_array($entity->getDocument()->getVehicle()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_DEPOT => static function (DocumentRecord $entity, ?array $args = null) {
                        return $entity->getDocument() && $entity->getDocument()->isVehicleDocument() && $entity->getDocument()->getVehicle()
                            && $entity->getDocument()->getVehicle()->getDepot()
                            && in_array($entity->getDocument()->getVehicle()->getDepot()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_GROUP => static function (DocumentRecord $entity, ?array $args = null) {
                        if ($entity->getDocument()
                            && $entity->getDocument()->isVehicleDocument()
                            && $entity->getDocument()->getVehicle()->getGroups()
                        ) {
                            $intersectValues = array_intersect(
                                $args,
                                $entity->getDocument()->getVehicle()->getGroups()->map(
                                    static function (VehicleGroup $g) {
                                        return $g->getId();
                                    }
                                )->toArray()
                            );

                            return (bool)count($intersectValues);
                        } else {
                            return false;
                        }
                    },
                    ScopeType::SUBTYPE_DRIVER => static function (DocumentRecord $entity, ?array $args = null) {
                        if ($entity->getDocument()
                            && $entity->getDocument()->isVehicleDocument()
                            && $entity->getDocument()->getVehicle()
                            && $entity->getDocument()->getVehicle()->getDriver()
                        ) {
                            return in_array($entity->getDocument()->getVehicle()->getDriver()->getId(), $args);
                        } elseif ($entity->getDocument()
                            && $entity->getDocument()->isDriverDocument()
                            && $entity->getDocument()->getDriver()
                        ) {
                            return in_array($entity->getDocument()->getDriver()->getId(), $args);
                        } else {
                            return false;
                        }
                    },
                ],
                ScopeType::REMINDER => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_VEHICLE => static function (Reminder $entity, ?array $args = null) {
                        return $entity->getVehicle() && in_array($entity->getVehicle()->getId(), $args);
                    },
                    ScopeType::SUBTYPE_DEPOT => static function (Reminder $entity, ?array $args = null) {
                        return $entity->getVehicle() && (($entity->getVehicle()->getDepot()
                                && in_array($entity->getVehicle()->getDepot()->getId(), $args)));
                    },
                    ScopeType::SUBTYPE_GROUP => static function (Reminder $entity, ?array $args = null) {
                        if ($entity->getVehicle() && $entity->getVehicle()->getGroups()) {
                            $intersectValues = array_intersect(
                                $args,
                                $entity->getVehicle()->getGroups()->map(
                                    static function (VehicleGroup $g) {
                                        return $g->getId();
                                    }
                                )->toArray()
                            );

                            return (bool)count($intersectValues);
                        } else {
                            return false;
                        }
                    },
                    ScopeType::SUBTYPE_DRIVER => static function (Reminder $entity, ?array $args = null) {
                        if ($entity->getVehicle() && $entity->getVehicle()->getDriver()) {
                            return in_array($entity->getVehicle()->getDriver()->getId(), $args);
                        } else {
                            return false;
                        }
                    },
                ],
                ScopeType::SERVICE_RECORD => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_VEHICLE => static function (ServiceRecord $entity, ?array $args = null) {
                        if ($entity->isServiceRecord()) {
                            return $entity->getReminder() && $entity->getReminder()->getVehicle()
                                && in_array($entity->getReminder()->getVehicle()->getId(), $args);
                        } elseif ($entity->isRepair()) {
                            return $entity->getRepairVehicle() && in_array($entity->getRepairVehicle()->getId(), $args);
                        } else {
                            return false;
                        }
                    },
                    ScopeType::SUBTYPE_DEPOT => static function (ServiceRecord $entity, ?array $args = null) {
                        if ($entity->isServiceRecord()) {
                            return $entity->getReminder() && $entity->getReminder()->getVehicle()
                                && $entity->getReminder()->getVehicle()->getDepot()
                                && in_array($entity->getReminder()->getVehicle()->getDepot()->getId(), $args);
                        } elseif ($entity->isRepair()) {
                            return $entity->getRepairVehicle() && $entity->getRepairVehicle()->getDepot()
                                && in_array($entity->getRepairVehicle()->getDepot()->getId(), $args);
                        } else {
                            return false;
                        }
                    },
                    ScopeType::SUBTYPE_GROUP => static function (ServiceRecord $entity, ?array $args = null) {
                        if ($entity->isServiceRecord()) {
                            if ($entity->getReminder()
                                && $entity->getReminder()->getVehicle()
                                && $entity->getReminder()->getVehicle()->getGroups()
                            ) {
                                $intersectValues = array_intersect(
                                    $args,
                                    $entity->getReminder()->getVehicle()->getGroups()->map(
                                        static function (VehicleGroup $g) {
                                            return $g->getId();
                                        }
                                    )->toArray()
                                );

                                return (bool)count($intersectValues);
                            } else {
                                return false;
                            }
                        } elseif ($entity->isRepair()) {
                            if ($entity->getRepairVehicle() && $entity->getRepairVehicle()->getGroups()) {
                                $intersectValues = array_intersect(
                                    $args,
                                    $entity->getRepairVehicle()->getGroups()->map(
                                        static function (VehicleGroup $g) {
                                            return $g->getId();
                                        }
                                    )->toArray()
                                );

                                return (bool)count($intersectValues);
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    },
                    ScopeType::SUBTYPE_DRIVER => static function (ServiceRecord $entity, ?array $args = null) {
                        if ($entity->isServiceRecord()
                            && $entity->getReminder()
                            && $entity->getReminder()->getVehicle()
                            && $entity->getReminder()->getVehicle()->getDriver()
                        ) {
                            return in_array($entity->getReminder()->getVehicle()->getDriver()->getId(), $args);
                        } elseif ($entity->isRepair()
                            && $entity->getRepairVehicle()
                            && $entity->getRepairVehicle()->getDriver()
                        ) {
                            return in_array($entity->getRepairVehicle()->getDriver()->getId(), $args);
                        } else {
                            return false;
                        }
                    },
                ],
                ScopeType::ASSET => [
                    ScopeType::SUBTYPE_ANY => static function () {
                        return true;
                    },
                    ScopeType::SUBTYPE_ASSET => static function (Asset $entity, ?array $args = null) {
                        return in_array($entity->getId(), $args);
                    }
                ],
                ScopeType::AREA => [
                    ScopeType::SUBTYPE_ANY => function (
                        $entity,
                        $args = null,
                        ?Notification $ntf = null,
                        array $context = []
                    ) {
                        return $this->checkAreaTriggerType($ntf, [], $context, ScopeType::SUBTYPE_ANY);
                    },
                    ScopeType::SUBTYPE_AREA => function (
                        $entity,
                        ?array $args = null,
                        ?Notification $ntf = null,
                        array $context = []
                    ) {
                        return $this->checkAreaTriggerType($ntf, $args, $context, ScopeType::SUBTYPE_AREA);
                    },
                    ScopeType::SUBTYPE_AREAS_GROUP => function (
                        $entity,
                        ?array $args = null,
                        ?Notification $ntf = null,
                        array $context = []
                    ) {
                        return $this->checkAreaTriggerType($ntf, $args, $context, ScopeType::SUBTYPE_AREAS_GROUP);
                    }
                ],
                ScopeType::ANY => [
                    ScopeType::ANY => static function () {
                        return true;
                    }
                ],
            ][$type->getType()][$type->getSubType()] ?? static function () {
                return false;
            };
    }

    public function checkAreaTriggerType(
        Notification $ntf,
        ?array $ids,
        ?array $context,
        string $subType,
        $entity = null
    ): bool {
        //special check for ntf events with area additional scope
        if (!in_array($ntf->getEvent()->getName(), [Event::VEHICLE_GEOFENCE_ENTER, Event::VEHICLE_GEOFENCE_LEAVE])) {
            if (!$ntf->getAreaTriggerType()) {
                return true;
            }

            if ($ntf->isAreaTriggerTypeEverywhere()) {
                return true;
            }
        }

        //hack for vehicle leave area
        if ($ntf->getEvent()->getName() === Event::VEHICLE_GEOFENCE_LEAVE) {
            if ($subType == ScopeType::SUBTYPE_ANY) {
                return true;
            } elseif ($ids && $subType == ScopeType::SUBTYPE_AREA) {
                return in_array($entity->getAreaId(), $ids);
            } elseif ($subType == ScopeType::SUBTYPE_AREAS_GROUP) {
                return (bool)array_intersect($ids, $entity->getArea()->getGroupsId());
            }
        }

        if (isset($context[EventLog::LAT]) && isset($context[EventLog::LNG])) {
            $result = false;

            if ($subType == ScopeType::SUBTYPE_ANY) {
                $result = $this->em->getRepository(Area::class)->findByPointAndIds(
                    $context[EventLog::LNG] . ' ' . $context[EventLog::LAT],
                    [],
                    [],
                    $ntf->getListenerTeam()
                );
            } elseif ($ids && $subType == ScopeType::SUBTYPE_AREA) {
                $result = $this->em->getRepository(Area::class)
                    ->findByPointAndIds($context[EventLog::LNG] . ' ' . $context[EventLog::LAT], $ids);
            } elseif ($subType == ScopeType::SUBTYPE_AREAS_GROUP) {
                $result = $this->em->getRepository(Area::class)
                    ->findByPointAndIds($context[EventLog::LNG] . ' ' . $context[EventLog::LAT], [], $ids);
            }

            if ($ntf->isAreaTriggerTypeInside()) {
                return $result;
            } elseif ($ntf->isAreaTriggerTypeOutside()) {
                return !$result;
            }

            return $result;
        }

        return false;
    }
}
