<?php

namespace App\Service\Vehicle;

use App\Entity\DriverHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Exceptions\ValidationException;

trait VehicleValidationTrait
{
    public function validateSetDriverFields(\DateTime $date, Vehicle $vehicle, User $driver)
    {
        if ($date > (new \DateTime())) {
            throw (new ValidationException())->setErrors(
                ['startDate' => ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')]]
            );
        }

        if ($vehicle->getTeam()->getId() !== $driver->getTeamId()) {
            throw (new ValidationException())->setErrors(
                ['driver' => ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')]]
            );
        }
    }

    public function validatePrevVehicleHistoryFinishDate(?DriverHistory $prevVehicleHistory, \DateTime $date)
    {
        if ($prevVehicleHistory && ($prevVehicleHistory->getFinishDate() > $date)) {
            $errors['startDate'] = ['wrong_value' => $this->translator->trans('entities.driverHistory.driver_history_already_exists')];
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function validatePrevVehicleHistoryDate(?DriverHistory $prevDriverHistory, \DateTime $date)
    {
        if ($prevDriverHistory && ($prevDriverHistory->getStartDate() > $date || ($prevDriverHistory->getFinishDate() > $date))) {
            $errors['startDate'] = ['wrong_value' => $this->translator->trans('entities.driverHistory.driver_history_already_exists')];
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function validateDriverNewDrivingDate(User $user, \DateTime $date)
    {
        $driverHistory = $this->em->getRepository(DriverHistory::class)->checkHistoryByDriverAndVehicle($user, $date);
        if ($driverHistory) {
            $errors['startDate'] = ['wrong_value' => $this->translator->trans('entities.driverHistory.driver_history_already_exists')];
            throw (new ValidationException())->setErrors($errors);
        }
    }
}