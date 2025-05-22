<?php

namespace App\Service\Xero;

use App\Entity\Team;
use App\Entity\Client;
use App\Entity\Xero\XeroClientAccount;
use App\Entity\Xero\XeroClientSecret;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as GuzzleHttpClient;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Configuration;

class XeroService
{
    private $xeroApiUrl = 'https://api.xero.com/api.xro/2.0/';
    private $xeroTokenUrl = 'https://identity.xero.com/connect/token';
    private $team;
    private $cacheKey;

    public function __construct(
        public EntityManager $em,
        private MemoryDbService $memoryDb,
        private Security $security,
        public TranslatorInterface $translator,
        public NotificationEventDispatcher $notificationDispatcher
    ) {

    }

    private function getCacheKey()
    {
        if ($this->cacheKey) {
            return $this->cacheKey;
        }

        $team = $this->getUserTeam();
        $this->cacheKey = 'xero_oauth2_' . $team->getId();

        return $this->cacheKey;
    }

    private function getUserTeam()
    {
        if ($this->team) {
            return $this->team;
        }

        if ($user = $this->security->getUser()) {
            return $user->getTeam();
        }

        throw new \Exception('Team is not set');
    }

    public function setUserTeam($team)
    {
        $this->team = $team;
    }

    private function checkSession()
    {
        $secret = $this->getUserTeam()->getXeroClientSecret();
        $token = $this->getToken();
        if (empty($token)) {
            $result = $this->sendAuthRequestToXero($secret->getXeroClientId(), $secret->getXeroClientSecret());

            if ($result) {
                $this->setToken($result->access_token, $result->token_type, $result->scope, $result->expires_in);
            }
        }
    }

    private function sendAuthRequestToXero($xeroClientId, $xeroClientSecret)
    {
        $client = new GuzzleHttpClient();
        $res = $client->request('POST', $this->xeroTokenUrl, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'scope' => 'accounting.transactions accounting.contacts accounting.transactions.read accounting.attachments accounting.contacts.read accounting.settings.read accounting.attachments.read accounting.settings accounting.journals.read accounting.budgets.read accounting.reports.tenninetynine.read accounting.reports.read',
            ],
            'auth' => [$xeroClientId, $xeroClientSecret]
        ]);

        $dataJson = $res->getBody()->getContents();

        if ($dataJson) {
            return json_decode($dataJson);
        }

        return null;
    }

    private function getSession()
    {
        $this->checkSession();
        return $this->memoryDb->getFromJson($this->getCacheKey());
    }

    /**
     * @param $token
     * @param $tokenType
     * @param $scope
     * @param null $expires
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function setToken($token, $tokenType, $scope, $expires = null)
    {
        $data = [
            'access_token' => $token,
            'expires_in' => $expires,
            'token_type' => $tokenType,
            'scope' => $scope,
        ];
        $this->memoryDb->setToJsonTtl($this->getCacheKey(), $data, [], 1800);
    }

    /**
     * @return mixed|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getToken()
    {
        $token = $this->memoryDb->getFromJson($this->getCacheKey());
        //If it doesn't exist or is expired, return null
        if (empty($token)
            || ($token['expires_in'] !== null
                && $token['expires_in'] <= time())
        ) {
            return null;
        }
        return $token;
    }

    private function getAccessToken()
    {
        $token = $this->getSession();
        return $token['access_token'];
    }

    /**
     * @param $token
     * @return IdentityApi
     */
    private function getIdentityApi($token = null)
    {
        $config = $this->getConfig($token);
        return new IdentityApi(
            new GuzzleHttpClient(),
            $config
        );
    }

    public function getAccountingApi()
    {
        return new AccountingApi(
            new GuzzleHttpClient(),
            $this->getConfig()
        );
    }

    public function getContacts(array $params)
    {
        $client = new GuzzleHttpClient();
        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Accept' => 'application/json',
        ];
        if (!isset($params['order'])) {
            $params['order'] = 'Name';
        }

        $res = $client->get($this->xeroApiUrl . 'Contacts',
            [
                //'debug'   => true,
                'headers' => $headers,
                'query' => $params
            ]);
        $clientsWithXero = $this->em->getRepository(Client::class)->getClientsWithXero();
        $clientsSorted = [];
        /** @var Client $xeroClient */
        foreach ($clientsWithXero as $xeroClient) {
            $clientsSorted[$xeroClient->getXeroClientAccount()->getXeroContactId()] = $xeroClient->toArray(['name']);
        }

        $dataJson = $res->getBody()->getContents();
        if ($dataJson) {
            $contacts = json_decode($dataJson);
            $data = [];
            foreach ($contacts->Contacts as $contact) {
                $data[] = [
                    'ContactID' => $contact->ContactID,
                    'Name' => $contact->Name,
                    'client' => $clientsSorted[$contact->ContactID] ?? null
                ];
            }
        }

        return $data;
    }

    public function create(array $data, Team $team)
    {
        try {
            $xeroClientSecret = new XeroClientSecret($data);
            $xeroClientSecret->setTeam($team);
            $this->em->persist($xeroClientSecret);
            $this->em->flush();

            return $xeroClientSecret;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(array $data, XeroClientSecret $xeroClientSecret)
    {
        // @todo why transaction was here?
//        $connection = $this->em->getConnection();

        try {
//            $connection->beginTransaction();
            $xeroClientSecret->setAttributes($data);
//            $xeroClientSecret->setXeroClientId($data['xeroClientId']);
//            $xeroClientSecret->setXeroClientSecret($data['xeroClientSecret']);
//            $xeroClientSecret->setXeroTenantId($data['tenant_id']);
            $this->em->persist($xeroClientSecret);
            $this->em->flush();

//            $this->em->getConnection()->commit();

            return $xeroClientSecret;
        } catch (\Exception $e) {
//            $connection->rollback();
            throw $e;
        }
    }

    public function createCurrentUserContact(array $data, Team $team, Client $client)
    {
        // @todo why transaction was here?
//        $connection = $this->em->getConnection();
        try {
//            $connection->beginTransaction();

            $xeroClientAccount = new XeroClientAccount();
            $xeroClientAccount->setXeroContactId($data['ContactID']);
            $xeroClientAccount->setTeam($team);
            $xeroClientAccount->setClient($client);
            $this->em->persist($xeroClientAccount);
            $this->em->flush();

//            $this->em->getConnection()->commit();

            return $xeroClientAccount;
        } catch (\Exception $e) {
//            $connection->rollback();
            throw $e;
        }
    }

    public function updateCurrentUserContact(array $data, XeroClientAccount $xeroClientAccount)
    {
        // @todo why transaction was here?
//        $connection = $this->em->getConnection();
        try {
//            $connection->beginTransaction();

            $xeroClientAccount->setXeroContactId($data['ContactID']);
            $this->em->persist($xeroClientAccount);
            $this->em->flush();

//            $this->em->getConnection()->commit();

            return $xeroClientAccount;
        } catch (\Exception $e) {
//            $connection->rollback();
            throw $e;
        }
    }

    public function hideXeroClientSecret(XeroClientSecret $xeroClientSecret): array
    {
        return array_merge($xeroClientSecret->toArray([
            'xeroTenantId',
            'xeroAccountPaymentId',
            'xeroAccountLineitemId'
        ]), [
            'xeroClientId' => substr($xeroClientSecret->getXeroClientId(), 0, 2) . str_repeat('*',
                    28) . substr($xeroClientSecret->getXeroClientId(), -2, 2),
            'xeroClientSecret' => substr($xeroClientSecret->getXeroClientSecret(), 0, 2) . str_repeat('*',
                    44) . substr($xeroClientSecret->getXeroClientSecret(), -2, 2),
        ]);
    }

    public function getConfig($token = null)
    {
        return Configuration::getDefaultConfiguration()->setAccessToken($token ?? (string)$this->getSession()['access_token']);
    }

    /**
     * @param $data
     * @param UserInterface $user
     * @return XeroClientSecret
     * @throws \XeroAPI\XeroPHP\ApiException
     */
    public function saveAuthParams($data, UserInterface $user)
    {
        $team = $user->getTeam();
        $xeroClientSecret = $this->em->getRepository(XeroClientSecret::class)->findOneBy(['team' => $team]);

        $credentials = $xeroClientSecret ? array_merge($xeroClientSecret->toArray(), $data) : $data;

        $response = $this->sendAuthRequestToXero($credentials['xeroClientId'], $credentials['xeroClientSecret']);

        if ($response) {
            if ($xeroClientSecret) {
                $xeroClientSecret = $this->update($data, $xeroClientSecret);
            } else {
                $xeroClientSecret = $this->create($data, $team);
            }

            $this->security->getUser()->getTeam()->setXeroClientSecret($xeroClientSecret);

            $organizations = $this->getConnections();
            if (count($organizations)) {
                $xeroClientSecret = $this->update(['xeroTenantId' => $organizations[0]['id']], $xeroClientSecret);
            }

            return $xeroClientSecret;
        }

        throw new \Exception('Something went wrong');
    }

    /**
     * @return \XeroAPI\XeroPHP\Models\Identity\Connection[]
     * @throws \XeroAPI\XeroPHP\ApiException
     */
    public function getConnections()
    {
        $connections = $this->getIdentityApi()->getConnections();
        $tenants = [];

        foreach ($connections as $connection) {
            $tenants[] = [
                'id' => $connection->getTenantId(),
                'name' => $connection->getTenantName(),
            ];
        }

        return $tenants;
    }
}
