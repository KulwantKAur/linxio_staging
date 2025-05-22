<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\Document;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Message;
use App\Entity\Notification\Notification;
use App\Entity\Role;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\EventListener\Notification\NotificationListener;
use App\Events\Notification\NotificationEvent;
use App\Mailer\MailSender;
use App\Mailer\Render\RenderedEmail;
use App\Service\EventLog\EventLogService;
use App\Service\EventLog\Manager\EventLogManager;
use App\Service\Notification\EntityPlaceholderService;
use App\Service\Notification\Placeholder\Factory\EventEntityHandlerFactory;
use App\Service\Notification\Placeholder\Factory\PlaceholderFactory;
use App\Service\Notification\Placeholder\Interfaces\EventEntityHandlerInterface;
use App\Service\Notification\Placeholder\Interfaces\PlaceholderInterface;
use App\Service\Notification\Queue\Consumer\EmailTransportConsumer;
use App\Service\Notification\Queue\Consumer\MobileAppTransportConsumer;
use App\Service\Notification\Queue\Consumer\NotificationEventConsumer;
use App\Service\Notification\Queue\Consumer\WebAppTransportConsumer;
use App\Service\Route\RoutePostHandle\RoutePostHandleConsumer;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Asserter;
use Fesor\JsonMatcher\JsonMatcher;
use Mockery\MockInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait NotificationTrait
{
    use Asserter;

    protected $savedValue;
    protected $notification;
    protected $eventObj;
    protected $eventMock;

    /**
     * @When I want get transports
     */
    public function iWantGetTransports()
    {
        $this->get('/api/notifications/transports');
    }

    /**
     * @When I want set transport setting :name with :value
     */
    public function iWantSetTransportSetting($name, $value)
    {
        $repo = $this->getEntityManager()->getRepository(Setting::class);

        foreach ($repo->findBy(['name' => $name]) as $setting) {
            /** @var Setting $setting */
            $setting->setValue((int)$value);
            $this->getEntityManager()->persist($setting);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @When I want get event types
     */
    public function iWantGetEventTypes()
    {
        $this->get('/api/notifications/event-types');
    }

    /**
     * @When I want get notifications
     */
    public function iWantGetNotifications()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/notifications?' . $params);
    }

    /**
     * @When I want get notification :id
     */
    public function iWantGetNotification(int $id)
    {
        $this->get(sprintf('/api/notifications/%d', $id));
    }

    /**
     * @When I want get last notification
     */
    public function iWantGetLastNotification()
    {
        $this->get(sprintf('/api/notifications/%d', $this->notification['id']));
    }

    /**
     * @When I want create notification
     */
    public function iWantCreateNotification()
    {
        $this->post('/api/notifications', $this->fillData);

        $this->notification = $this->getResponseData();
    }

    /**
     * @When I want update last notification
     */
    public function iWantUpdateLastNotification()
    {
        $this->patch(sprintf('/api/notifications/%d', $this->notification['id']), $this->fillData);

        $this->notification = $this->getResponseData();
    }

    /**
     * @When I want delete last notification
     */
    public function iWantDeleteLastNotification()
    {
        $this->delete(sprintf('/api/notifications/%d', $this->notification['id']));
    }

    /**
     * @When I want export event log list
     */
    public function iWantExportEventLog()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/event-log/csv?' . $params);
    }

    /**
     * @When I want get alert setting
     */
    public function iWantGetAlertSetting()
    {
        $params = http_build_query($this->fillData);

        $this->get('/api/notifications/alert/setting?' . $params);
    }

    /**
     * @When I want get generated notification messages
     */
    public function iWantGetNotificationMessages()
    {
        $params = http_build_query($this->fillData);

        $this->get('api/notifications/messages?' . $params);
    }

    /**
     * @When I want mark read notification message with id :id
     */
    public function iWantGetMarkReadNotificationMessages($id)
    {
        $params = http_build_query($this->fillData);

        $this->post(sprintf('api/notifications/messages/%d/read', $id), $params);
    }

    /**
     * @When I want mark read all notification messages by active user
     */
    public function iWantMarkReadAllNotificationMessages()
    {
        $params = http_build_query($this->fillData);

        $this->post('api/notifications/messages/read', $params);
    }

    /**
     * @When  I want get count unread notification messages
     */
    public function iWantGetCountNotificationMessages()
    {
        $params = http_build_query($this->fillData);

        $this->get('api/notifications/messages/count?' . $params);
    }

    /**
     * @When I want get list event tracking days
     */
    public function iWantGetListEventTrackingDays()
    {
        $this->get('/api/notifications/list-days');
    }

    /**
     * @When I want fill :field field with :value
     */
    public function iFillData($name, $value)
    {
        $this->handleFillEvent($value);
        $this->handleFillUser($value);
        $this->handleFillUserGroup($value);
        $this->handleFillRole($value);
        $this->handleFillVehicle($value);

        parent::iFillData($name, $value);
    }

    private function handleFillEvent(&$value)
    {
        $eventPattern = vsprintf(
            '~(event\((%s)\)|event\((%s)\s*,\s*(%s)\))~',
            [
                implode('|', Event::ALLOWED_EVENTS),
                implode('|', Event::ALLOWED_EVENTS),
                implode('|', Event::ALLOWED_TYPES),
            ]
        );

        if (1 === preg_match($eventPattern, $value, $eventParam)) {
            /** @var Event $event */
            if (3 === count($eventParam)) {
                $event = $this->getEntityManager()->getRepository(Event::class)->findOneBy(['name' => $eventParam[2]]);
            } else {
                $event = $this->getEntityManager()
                    ->getRepository(Event::class)
                    ->findOneBy(['name' => $eventParam[3], 'type' => $eventParam[4]]);
            }

            if (null === $event) {
                throw new \Exception('Invalid event param');
            }

            $value = $event->getId();
        }
    }

    private function handleFillUser(&$value)
    {
        if (1 === preg_match('~user\((.+)\)~', $value, $eventParam)) {
            /** @var Event $event */
            $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $eventParam[1]]);
            if (null === $user) {
                throw new \Exception('Invalid user email');
            }

            $value = $user->getId();
        }
    }

    private function handleFillUserGroup(&$value)
    {
        if (1 === preg_match('~user_group\((.+)\)~', $value, $eventParam)) {
            /** @var UserGroup $userGroup */
            $userGroup = $this->getEntityManager()->getRepository(UserGroup::class)->findOneBy(
                ['name' => $eventParam[1]]
            );
            if (null === $userGroup) {
                throw new \Exception('Invalid user group name');
            }

            $value = $userGroup->getId();
        }
    }

    private function handleFillRole(&$value)
    {
        $eventPattern = vsprintf(
            '~role\((%s)\s*,\s*(%s)\)~',
            [
                implode('|', Role::ALLOWED_ROLES),
                implode('|', ['admin', 'client']),
            ]
        );

        if (1 === preg_match($eventPattern, $value, $eventParam)) {
            /** @var Role $role */
            $role = $this->getEntityManager()
                ->getRepository(Role::class)
                ->findOneBy(['name' => $eventParam[1], 'team' => $eventParam[2]]);

            if (null === $role) {
                throw new \Exception('Invalid role param');
            }

            $value = $role->getId();
        }
    }

    private function handleFillVehicle(&$value)
    {
        if (1 === preg_match('~vehicle\((.+)\)~', $value, $param)) {
            /** @var Vehicle $event */
            $vehicle = $this->getEntityManager()->getRepository(Vehicle::class)->findOneBy(['regNo' => $param[1]]);
            if (null === $vehicle) {
                throw new \Exception('Invalid vehicle regNo');
            }

            $value = $vehicle->getId();
        }
    }

    /**
     * @When I want set mock Email Consumer :email :subject :body
     */
    public function setMockMailSender($email, $subject, $body)
    {
        $mailSenderMock = \Mockery::mock(MailSender::class);
        $mailSenderMock->shouldReceive('sendEmail')
            ->withArgs(
                static function ($emailArg, $msgArg) use ($email, $subject, $body) {
                    /** @var RenderedEmail $arg */
                    return isset($emailArg[0])
                        && $email === $emailArg[0]
                        && is_object($msgArg)
                        && $msgArg instanceof RenderedEmail
                        && $subject === $msgArg->subject()
                        && $body === $msgArg->body();
                }
            );
        $logger = \Mockery::mock($this->getContainer()->get('logger'));
        $this->getKernel()->getContainer()->set(
            'app.notification.email_transport_consumer',
            new EmailTransportConsumer($this->getEntityManager(), $mailSenderMock, $logger)
        );
    }

    /**
     * @When I want create notification message
     */
    public function iWantCreateNotificationMessage()
    {
        $notificationMessage = new Message();

        $class = new \ReflectionClass(Message::class);

        foreach ($this->fillData as $name => $value) {
            $setter = sprintf('set%s', ucfirst($name));

            if (method_exists($notificationMessage, $setter)) {
                $method = $class->getMethod($setter);
                $param = $method->getParameters()[0];

                $paramType = $param->getType() && $param->getType()->getName() ? $param->getType()->getName() : null;

                if ($paramType
                    && class_exists($paramType)
                    && (is_subclass_of($paramType, \DateTime::class)
                        || $paramType === \DateTime::class)) {
                    $value = new \DateTime($value);
                }

                $notificationMessage->$setter($value);
            } else {
                throw new \Exception('Invalid field');
            }
        }

        $this->getEntityManager()->persist($notificationMessage);
        $this->getEntityManager()->flush();
    }

    /**
     * @When I want call email consumer
     */
    public function iWantCallEmailConsumer()
    {
        /** @var EmailTransportConsumer $consumer */
        $consumer = $this->getKernel()->getContainer()->get('app.notification.email_transport_consumer');
        $messageStub = \Mockery::mock(AMQPMessage::class);
        $messageStub->shouldReceive('getBody')
            ->andReturn(
                json_encode(
                    ['id' => $this->getEntityManager()->getRepository(Message::class)->findAll()[0]->getId()]
                )
            );

        $consumer->execute($messageStub);
    }

    /**
     * @When I want fill :type user mock with id :id
     */
    public function iFillUserMock($type, $id)
    {
        if (!in_array($type, ['admin', 'client'])) {
            throw new \InvalidArgumentException();
        }

        $teamMock = \Mockery::mock(Team::class);
        $teamMock->shouldReceive('isAdminTeam')->andReturn(['client' => false, 'admin' => true][$type] ?? false);
        $teamMock->shouldReceive('getId')->andReturn(['client' => 2, 'admin' => 1][$type] ?? 2);
        $teamMock->shouldReceive('getClientName')->andReturn('Team Name');
        $teamMock->shouldReceive('getType')->andReturn('admin');

        $userMock = \Mockery::mock(User::class);
        $userMock->shouldReceive('getId')->andReturn($id);
        $userMock->shouldReceive('getTeam')->andReturn($teamMock);
        $userMock->shouldReceive('getEmail')->andReturn('test@ocsico.com');
        $userMock->shouldReceive('getBlockingMessage')->andReturn('message');
        $userMock->shouldReceive('getFullName')->andReturn('test user');
        $userMock->shouldReceive('getUpdatedByName')->andReturn('Full name');
        $userMock->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $userMock->shouldReceive('toArray')->andReturn(['client' => false, 'admin' => true]);

        $this->fillData['userMock'] = $userMock;
    }

    /**
     * @When I want fill event mock :alias :type
     */
    public function iFillEventMock($alias, $type)
    {
        $eventMock = \Mockery::mock(Event::class);
        $eventMock->shouldReceive('getName')->andReturn($alias);
        $eventMock->shouldReceive('getType')->andReturn($type);

        $this->fillData['eventMock'] = $eventMock;
    }

    /**
     * @When I want fill last notification
     */
    public function iFillLastNotification()
    {
        $notification = $this->getEntityManager()
            ->getRepository(Notification::class)
            ->findOneBy(['id' => $this->notification['id']]);

        $this->fillData['notification'] = $notification;
    }

    /**
     * @When I want fill event log for notification :eventName :type
     */
    public function iFillEventLogNotification($eventName, $type)
    {
        $eventId = $this->getEntityManager()
            ->getRepository(Event::class)
            ->findOneBy(['name' => $eventName, 'type' => $type]);

        $eventLog = $this->getEntityManager()
            ->getRepository(EventLog::class)
            ->findOneBy(['event' => $eventId]);

        $this->fillData['eventLog'] = $eventLog;
    }

    /**
     * @When Method EntityPlaceholderService->getUserFrontendLink :appFrontUrl must return :link
     */
    public function iWantTestEntityPlaceholderServiceGetUserFrontendLink($appFrontUrl, $link)
    {
        $service = new EntityPlaceholderService($appFrontUrl);

        $this->assertEquals(
            $link,
            $this->callProtectedMethod($service, 'getUserFrontendLink', [$this->fillData['userMock']])
        );
    }

    /**
     * @When I want check EntityPlaceholderService
     */
    public function iWantTestEntityPlaceholderService()
    {
        $event = $this->eventMock;
        $entity = $this->eventObj;
        $appFrontUrl = 'https://url';

        $mockEventEntityHandlerFactory = \Mockery::mock(sprintf('%s', EventEntityHandlerFactory::class))->makePartial();
        $mockPlaceholderFactory = \Mockery::mock(sprintf('%s', PlaceholderFactory::class))->makePartial();

        /** @var EntityPlaceholderService $mockService */
        $mockService = \Mockery::mock(
            EntityPlaceholderService::class,
            [
                $appFrontUrl,
                $mockPlaceholderFactory,
                $mockEventEntityHandlerFactory
            ]
        )->makePartial();

        $this->savedValue = $mockService->generatePlaceholders(
            $event,
            $entity
        );

        var_dump($this->savedValue);
    }

    /**
     * @Then I see in saved value field :field filled with :value
     */
    public function iSeeFieldFilledWithInSavedValue($value, $field)
    {
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'null') {
            $value = null;
        }

        if (!is_numeric($value)) {
            $value = json_encode($value);
        }

        if (strpos($value, 'string(') !== false) {
            $value = preg_replace('/\D/', '', $value) . '';
            $value = json_encode($value);
        }

        try {
            JsonMatcher::create(json_encode($this->savedValue))->equal($value, ['at' => "$field"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . ' ' . json_encode($this->savedValue));
        }
    }

    /**
     * @Then I see in saved value field :field
     */
    public function iSeeFieldInSavedValue($field)
    {
        try {
            JsonMatcher::create(json_encode($this->savedValue))->hasPath($field);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . ' ' . json_encode($this->savedValue));
        }
    }

    /**
     * @param $object
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public function callProtectedMethod($object, string $method, array $arguments)
    {
        $method = new \ReflectionMethod(get_class($object), $method);
        $method->setAccessible(true);

        return $method->invoke($object, ...$arguments);
    }

    /**
     * @When I want handle event log event :eventName :userEmail
     */
    public function iWantSetEventLog($eventName, $userEmail)
    {
        $em = $this->getEntityManager();
        $eventLogManager = \Mockery::mock(EventLogManager::class)->makePartial();

        /** @var EventLogService|MockInterface $EventLogService */
        $EventLogService = \Mockery::mock(
            EventLogService::class,
            [
                $em,
                $this->getKernel()->getContainer()->get('fos_elastica.finder.eventLog'),
                $eventLogManager,
                $this->getKernel()->getContainer()->get('translator')
            ]
        )->makePartial();

        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($userEmail);
        $user->setBlockingMessage('message');

        /** @var NotificationEvent|MockInterface $notificationMock */
        $notificationMock = \Mockery::mock(
            NotificationEvent::class,
            [$eventName, $user]
        )->makePartial();

        $producer = \Mockery::mock(Producer::class)->makePartial();
        $producer->shouldReceive('publish')->andReturn([])->once();

        /** @var NotificationListener|MockInterface $listenerMock */
        $listenerMock = \Mockery::mock(
            sprintf('%s[processingNotificationEvent]', NotificationListener::class),
            [$em, $producer, $EventLogService]
        )->makePartial();

        $listenerMock->processingNotificationEvent($notificationMock);
    }

    /**
     * @When I want handle notification event :eventName :eventType :data and send messages
     */
    public function iWantSetEventLogTest($eventName, $eventType, $data)
    {
        $em = $this->getEntityManager();
        $eventLogManager = \Mockery::mock(EventLogManager::class)->makePartial();

        /** @var EventLogService|MockInterface $EventLogService */
        $EventLogService = \Mockery::mock(
            EventLogService::class,
            [
                $em,
                $this->getKernel()->getContainer()->get('fos_elastica.finder.eventLog'),
                $eventLogManager,
                $this->getKernel()->getContainer()->get('translator')
            ]
        )->makePartial();

        switch ($eventType) {
            case 'user':
                /** @var User $entity */
                $entity = $em->getRepository(User::class)->findOneByEmail($data);
                break;
            case 'document':
                /** @var Document $entity */
                $entity = $em->getRepository(Document::class)->findOneBy(['title' => $data]);
                break;
            case 'trackerHistory':
                /** @var TrackerHistory $entity */
                $entity = $em->getRepository(TrackerHistory::class)->findOneBy(['id' => $data]);
                break;
            default:
                break;
        }

        /** @var NotificationEvent|MockInterface $notificationMock */
        $notificationMock = \Mockery::mock(
            NotificationEvent::class,
            [$eventName, $entity]
        )->makePartial();

        $producer = \Mockery::mock(Producer::class)->makePartial();
        $producer->shouldReceive('publish')->andReturn([])->once();

        /** @var NotificationListener|MockInterface $listenerMock */
        $listenerMock = \Mockery::mock(
            sprintf('%s[processingNotificationEvent]', NotificationListener::class),
            [$em, $producer, $EventLogService]
        )->makePartial();

        $listenerMock->processingNotificationEvent($notificationMock);

        $notificationCollectorService = \Mockery::mock(
            $this->getContainer()->get('App\Service\Notification\NotificationCollectorService')
        );

        $logger = \Mockery::mock($this->getContainer()->get('logger'));
        /** @var NotificationEventConsumer $consumer */
        $notificationEventConsumer = \Mockery::mock(
            NotificationEventConsumer::class,
            [
                $this->getEntityManager(),
                $notificationCollectorService,
                $logger
            ]
        )->makePartial();

        $eventMessage = new AMQPMessage(
            array_pop($this->getQueuedMessages('notification', 'events'))
        );

        $notificationEventConsumer->execute($eventMessage);
    }

    /**
     * @When I want send messages in queue notification_events
     */
    public function sendMessage()
    {
        $notificationCollectorService = \Mockery::mock(
            $this->getContainer()->get('App\Service\Notification\NotificationCollectorService')
        );

        /** @var NotificationEventConsumer $consumer */
        $notificationEventConsumer = \Mockery::mock(
            NotificationEventConsumer::class,
            [
                $this->getEntityManager(),
                $notificationCollectorService,
                $this->logger
            ]
        )->makePartial();

        $eventMessages = $this->getQueuedMessages('notification', 'events');

        foreach ($eventMessages as $eventMessage) {
            $notificationEventConsumer->execute(
                new AMQPMessage($eventMessage)
            );
        }
    }

    /**
     * @When I want send messages in queue notification_post_handle
     */
    public function sendPostHandelMessages()
    {
        $routeService = \Mockery::mock(
            $this->getContainer()->get('app.route_service')
        );

        /** @var RoutePostHandleConsumer $consumer */
        $postHandelConsumer = \Mockery::mock(
            RoutePostHandleConsumer::class,
            [
                $this->getEntityManager(),
                $this->logger,
                $routeService
            ]
        )->makePartial();

        $eventMessages = $this->getQueuedMessages('routes', 'routes_post_handle', 'routes.post_handle');

        foreach ($eventMessages as $eventMessage) {
            $postHandelConsumer->execute(
                new AMQPMessage($eventMessage)
            );
        }
    }

    /**
     * @Given Notifications send
     */
    public function notificationSend()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'app:notifications:send'
            ]
        );

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @When I want send messages in queue notification_webapp
     */
    public function sendWebMessages()
    {
        /** @var WebAppTransportConsumer $webAppConsumerSimulator */
        $webAppConsumerSimulator = \Mockery::mock(
            WebAppTransportConsumer::class,
            [
                $this->getEntityManager(),
                $this->getContainer()->getParameter('tracker_provider_url'),
                $this->getContainer()->getParameter('tracker_provider_secret'),
                $logger = \Mockery::mock($this->getContainer()->get('logger'))
            ]
        )->makePartial();

        $eventMessage = new AMQPMessage(
            array_pop($this->getQueuedMessages('notification', 'webapp'))
        );

        $webAppConsumerSimulator->execute($eventMessage);
    }

    /**
     * @When I want send messages in queue notification mobile_app
     */
    public function sendMobilePush()
    {
        $fcmService = \Mockery::mock($this->getContainer()->get('App\Service\Firebase\FCMService'));
        $logger = \Mockery::mock($this->getContainer()->get('logger'));

        /** @var MobileAppTransportConsumer $mobileAppConsumerSimulator */
        $mobileAppConsumerSimulator = \Mockery::mock(
            MobileAppTransportConsumer::class,
            [
                $this->getEntityManager(),
                $fcmService,
                $logger
            ]
        )->makePartial();

        $eventMessage = new AMQPMessage(
            array_pop($this->getQueuedMessages('notification', 'mobileapp'))
        );

        $mobileAppConsumerSimulator->execute($eventMessage);
    }

    /**
     * @When I want update ntf message acknowledge with id :id
     * @param $id
     */
    public function iWantUpdateNotificationAcknowledge($id)
    {
        $this->post('api/notifications/messages/' . $id . '/acknowledge', $this->fillData);
    }

    /**
     * @Given /^I should get an email with field "([^"]*)" containing:$/
     *
     * @param string $field
     * @param PyStringNode $body
     * @throws \Exception
     */
    public function iShouldGetTextAnEmail($field, PyStringNode $body)
    {
        $body = '"' . implode("", $body->getStrings()) . '"';
        try {
            $this->jsonResponse()
                ->equal($body, ['at' => "$field"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @When I want disabled all default notification
     */
    public function disabledSystemNTF()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->update(Notification::class, 'ntf')
            ->set('ntf.status', ':status')
            ->where('ntf.status IS NOT NULL')
            ->setParameter('status', 'disabled')
            ->getQuery()
            ->execute();
    }


    /**
     * @When I want get event object to save
     */
    public function iWantGetEventObject()
    {
        $em = $this->getEntityManager();
        $eventLogId = $this->eventData->data[0]->id ?? null;

        $eventLog = $em->getRepository(EventLog::class)->findOneBy(['id' => $eventLogId]);
        $entity = $em->getRepository($eventLog->getEvent()->getEntity())
            ->findOneBy(['id' => $eventLog->getDetails()['id']]);
        $notification = $em->getRepository(Notification::class)->findOneBy(['id' => $this->notification['id']]);

        $this->eventObj = $entity;
        $this->eventMock = $eventLog->getEvent();
        $this->notification = $notification;
    }

    /**
     * @Given /^I should get an email with field "([^"]*)" containing to template:$/
     *
     * @param string $field
     * @param PyStringNode $body
     * @throws \Exception
     */
    public function iShouldGetTextAnEmailRegexp($field, PyStringNode $body)
    {
        $body = '"' . implode("", $body->getStrings()) . '"';

        $placeholderByNtf = $this->notification->toArray(Notification::NOTIFICATION_PLACEHOLDERS);

        $bodyProcessing = $this->replace($body, array_merge($this->savedValue, $placeholderByNtf));
        try {
            $this->jsonResponse()
                ->equal($bodyProcessing, ['at' => "$field"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @param string $tpl
     * @param array $placeholders
     * @return string
     */
    private function replace(string $tpl, array $placeholders): string
    {
        return str_replace(
            array_map(
                static function ($v) {
                    return sprintf('${%s}', $v);
                },
                array_keys($placeholders)
            ),
            array_values($placeholders),
            $tpl
        );
    }
}
