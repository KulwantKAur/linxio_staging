<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\Client;
use App\Entity\Note;
use App\Entity\Role;
use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait ClientsTrait
{
    protected $clientData = null;

    /**
     * @When I want register client and remember
     */
    public function iWantRegisterClientRemember()
    {
        $response = $this->post('/api/clients', $this->fillData);

        $this->clientData = json_decode($response->getResponse()->getContent());
    }

    /**
     * @When I want register user for current client
     */
    public function iWantRegisterUserForCurrentClient()
    {
        $this->post('/api/clients/' . $this->clientData->id . '/users', $this->fillData);
    }

    /**
     * @When I want to register driver for current client
     */
    public function iWantToRegisterDriverForCurrentClient()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $role = $em->getRepository(Role::class)->findOneBy(['name' => Role::ROLE_CLIENT_DRIVER]);

        if (!$role) {
            throw new \Exception('You should create role: ' . Role::ROLE_CLIENT_DRIVER);
        }

        $this->fillData['roleId'] = $role->getId();
        $this->post('/api/clients/' . $this->clientData->id . '/users', $this->fillData);
    }

    /**
     * @When I want to update current client's user by email :email
     */
    public function iWantToUpdateCurrentClientUser($email)
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception("User with email: $email is not found");
        }

        $this->post('/api/clients/' . $this->clientData->id . '/users/' . $user->getId(), $this->fillData);
    }

    /**
     * @When I want register user for client with id :id
     */
    public function iWantRegisterUserForClient($id)
    {
        $this->post('/api/clients/' . $id . '/users', $this->fillData);
    }

    /**
     * @When I want get client plans
     */
    public function iWantGetClientPlans()
    {
        $this->get('/api/plans');
    }

    /**
     * @When I want get client by id :id
     */
    public function iWantGetClientById($id)
    {
        $this->get('/api/clients/' . $id);
    }

    /**
     * @When I want get remembered client
     */
    public function iWantGetRememberedClient()
    {
        $this->get('/api/clients/' . $this->clientData->id);
    }

    /**
     * @When I want get client :id users
     */
    public function iWantGetClientIdUsers($id)
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/clients/' . $id . '/users?' . $params);
    }

    /**
     * @When I want get current client users
     */
    public function iWantGetCurrentClientUsers()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/clients/' . $this->clientData->id . '/users?' . $params);
    }

    /**
     * @When I want get client :clientId user :userId
     */
    public function iWantGetClientIdUserId($clientId, $userId)
    {
        $this->get('/api/clients/' . $clientId . '/users/' . $userId);
    }

    /**
     * @When I want update client :clientName
     */
    public function iWantUpdateClient($clientName)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);
        $this->patch('/api/clients/' . $client->getId(), $this->fillData);

        $this->clientData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want get client status history :clientName
     */
    public function iWantGetClientStatusHistory($clientName)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);

        $this->get('/api/clients/' . $client->getId() . '/history/status');
    }

    /**
     * @When I want to know client :clientName exists in entity history
     */
    public function iWantToKnowClientExistInEntityHistory($clientName)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);
        $createdHistory = $this->getContainer()->get('app.entity_history_service')->getByEntityIdAndType(
            $client->getId(), EntityHistoryTypes::CLIENT_CREATED
        );

        if (!$createdHistory) {
            throw new \Exception('Create history is not found');
        }
    }

    /**
     * @When I want get client update history :clientName
     */
    public function iWantGetClientUpdateHistory($clientName)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);

        $this->get('/api/clients/' . $client->getId() . '/history/updated');
    }

    /**
     * @When I want register admin team user
     */
    public function iWantRegisterAdminTeamUser()
    {
        $this->post('/api/users/admin', $this->fillData);
    }

    /**
     * @When I want add client note to DB for client :clientName created by :userEmail
     */
    public function iWantAddNoteToDb(string $clientName, string $userEmail)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);
        $user = $em->getRepository(User::class)->findByEmail($userEmail);

        $note = new Note(
            array_merge(
                $this->fillData,
                [
                    'client' => $client,
                    'createdBy' => $user,
                ]
            )
        );

        $em->persist($note);
        $em->flush();
    }

    /**
     * @When I want get client notes for client :clientName by type :type
     */
    public function iWantGetNotesByType(string $clientName, string $type)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);

        $this->get(sprintf('/api/client-notes/%d/%s', $client->getId(), $type));
    }

    /**
     * @When I want to update client user with client name :clientName and user email :userEmail
     */
    public function iWantUpdateClientUserWithClientNameAndUserEmail(string $clientName, string $userEmail)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($userEmail);
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);
        $em->clear();
        $this->post(sprintf('/api/clients/%d/users/%d', $client->getId(), $user->getId()), $this->fillData);
    }

    /**
     * @When I want delete client user with client name :clientName and user email :userEmail
     */
    public function iWantDeleteClientUserWithClientNameAndUserEmail(string $clientName, string $userEmail)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($userEmail);
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);

        $this->delete(sprintf('/api/clients/%d/users/%d', $client->getId(), $user->getId()));
    }

    /**
     * @When I want set key contact :userEmail for client :clientName
     */
    public function iWantSetKeyContact(string $clientName, string $userEmail)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($userEmail);
        /** @var Client $client */
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $clientName]);
        $client->setKeyContact($user);

        $em->flush();
    }

    /**
     * @When I want get admin team users
     */
    public function iWantGetAdminTeamUsers()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/admin/users?' . $params);
    }

    /**
     * @When I want export clients list
     */
    public function iWantExportClientsList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/clients/csv?' . $params);
    }

    /**
     * @When I want fill teamId by saved clientId
     */
    public function iWantFillClientIdBySavedClientId()
    {
        $this->fillData['teamId'] = $this->clientData->team->id;
    }

    /**
     * @When I want to fill teamId by team of current user
     */
    public function iWantToFillTeamIdByTeamOfCurrentUser()
    {
        $this->fillData['teamId'] = $this->authorizedUser->getTeam()->getId();
    }

    /**
     * @When I signed in as :role team :team and client teamId
     */
    public function iWantLoginWithClientTeam($role, $team)
    {
        $this->iSignedAsTeamIdUser($role, $team, $this->clientData->team->id);
    }

    /**
     * @When I want to set team ID by team ID of following user :email
     */
    public function IWantToSetTeamIdByIdOfGivenUser($email)
    {
        $criteria = [
            'email' => $email,
        ];
        $user =  $this->getEntityManager()->getRepository(User::class)->findOneBy($criteria);

        $this->fillData['teamId'] = $user->getTeam()->getId();
    }

    /**
     * @Given Client demo update status
     */
    public function clientDemoUpdateStatus()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'app:client-demo:update-status'
            ]
        );

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }
}
