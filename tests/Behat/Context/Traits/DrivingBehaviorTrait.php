<?php

namespace App\Tests\Behat\Context\Traits;

use App\Command\Tracker\RecalculateIdlingCommand;
use App\Command\Tracker\RecalculateSpeedingCommand;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait DrivingBehaviorTrait
{

    /**
     * @When I want get vehicle total driving behavior stats for saved vehicle id
     */
    public function iWantGetVehicleTotalDrivingBehavior()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/vehicle/' . $this->vehicleData->id . '/total?' . $params);
    }

    /**
     * @When I want get vehicle scores for driving behavior for saved vehicle id
     */
    public function iWantGetVehicleScoresDrivingBehavior()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/vehicle/' . $this->vehicleData->id . '/scores?' . $params);
    }

    /**
     * @When I want get vehicle summary
     */
    public function iWantGetVehicleSummary()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/summary/vehicle?' . $params);
    }

    /**
     * @When I want get vehicle idling for saved vehicle id
     */
    public function iWantGetVehicleIdling()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/details/' . $this->vehicleData->id . '/idling?' . $params);
    }

    /**
     * @When I want get vehicle speeding for saved vehicle id
     */
    public function iWantGetVehicleSpeeding()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/vehicle/' . $this->vehicleData->id . '/speeding?' . $params);
    }

    /**
     * @When I want get vehicle harsh-cornering for saved vehicle id
     */
    public function iWantGetVehicleHarshCornering()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/details/' . $this->vehicleData->id . '/harsh-cornering?' . $params);
    }

    /**
     * @Given Calculate idling
     */
    public function calculateIdling()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => RecalculateIdlingCommand::getDefaultName()
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @Given Calculate speeding
     */
    public function calculateSpeeding()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => RecalculateSpeedingCommand::getDefaultName()
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @When I want get driver total driving behavior stats for authorized user
     */
    public function iWantGetDriverTotalDrivingBehavior()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/driver/' . $this->authorized_user->getId() . '/total?' . $params);
    }

    /**
     * @When I want get driver total driving behavior stats for team user
     */
    public function iWantGetDriverTotalDrivingBehaviorForTeamUser()
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(
            ['team' => $this->clientData->team->id]
        );
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/driver/' . $user->getId() . '/total?' . $params);
    }

    /**
     * @When I want get driver summary
     */
    public function iWantGetDriverSummary()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/driver/summary?' . $params);
    }

    /**
     * @When I want get driver scores for driving behavior for saved driver id
     */
    public function iWantGetDriverScoresDrivingBehavior()
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(
            ['team' => $this->clientData->team->id]
        );
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/driver/' . $user->getId() . '/scores?' . $params);
    }

    /**
     * @When I want to get speeding grouped by vehicle using query :query
     */
    public function iWantToGetSpeedingGroupedByVehicle($query)
    {
        $this->get('/api/reports/speeding-by-vehicle' . $query);
    }

    /**
     * @When I want to get speeding for given vehicle using query :query
     */
    public function iWantToGetSpeedingForGivenVehicle($query)
    {
        $this->get('/api/reports/speeding/vehicle/' . $this->vehicleData->id . $query);
    }

    /**
     * @When I want to get speeding grouped by driver using query :query
     */
    public function iWantToGetSpeedingGroupedByDriver($query)
    {
        $this->get('/api/reports/speeding-by-driver' . $query);
    }

    /**
     * @When I want to get speeding for driver :email using query :query
     */
    public function iWanToGetSpeedingForGivenDriver($email, $query)
    {
        $criteria = [
            'email' => $email,
        ];
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy($criteria);
        $this->get('/api/reports/speeding/driver/' . $user->getId() . $query);
    }

    /**
     * @When I want get vehicle eco-speed
     */
    public function iWantGetVehicleEcoSpeed()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/details/' . $this->vehicleData->id . '/eco-speed?' . $params);
    }

    /**
     * @When I want get driver eco-speed
     */
    public function iWantGetDriverEcoSpeed()
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(
            ['team' => $this->clientData->team->id]
        );
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/driver/details/' . $user->getId() . '/eco-speed?' . $params);
    }

    /**
     * @When I want get driver idling
     */
    public function iWantGetDriverIdling()
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(
            ['team' => $this->clientData->team->id]
        );
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/driver/details/' . $user->getId() . '/idling?' . $params);
    }

    /**
     * @When I want to get vehicle summary csv
     */
    public function iWantGetVehicleSummaryCSV()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/summary/vehicle/csv?' . $params);
    }

    /**
     * @When I want to get driver summary csv
     */
    public function iWantGetDriverSummaryCSV()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/driving-behavior/driver/summary/csv?' . $params);
    }

    /**
     * @When I want get team vehicles total distance
     */
    public function iWantGetTeamVehiclesTotalDistance()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/dashboard/vehicles/distance?' . $params);
    }

    /**
     * @When I want get team drivers total distance
     */
    public function iWantGetTeamDriversTotalDistance()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/dashboard/drivers/distance?' . $params);
    }

    /**
     * @When I want get driving behavior dashboard
     */
    public function iWantGetDrivingBehaviorDashboard()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/dashboard/driving-behavior?' . $params);
    }

    /**
     * @When I want get driving behavior dashboard by date range
     */
    public function iWantGetDrivingBehaviorDashboardByDateRange()
    {
        $params = http_build_query($this->fillData);

        return $this->get('/api/dashboard/driving-behavior/range?' . $params);
    }
}
