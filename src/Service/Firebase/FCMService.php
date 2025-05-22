<?php

namespace App\Service\Firebase;

use App\Entity\Notification\Message;
use App\Entity\Notification\NotificationMobileDevice;
use App\Util\ExceptionHelper;
use Google\Client as GoogleClient;
use Google\Service\FirebaseCloudMessaging;
use Google\Service\FirebaseCloudMessaging\AndroidConfig;
use Google\Service\FirebaseCloudMessaging\AndroidNotification;
use Google\Service\FirebaseCloudMessaging\ApnsConfig;
use Google\Service\FirebaseCloudMessaging\Message as FCMMsg;
use Google\Service\FirebaseCloudMessaging\Notification as FCMNtf;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

class FCMService
{
    private const FCM_PROJECT_ID = 'linxio-7dded';

    private function initGoogleClient(): GoogleClient
    {
        try {
            $googleClient = new GoogleClient();
            $googleClient->setHttpClient($this->initHttpClient());
            $googleClient->setAuthConfig($this->googleAppConfig);
            $googleClient->addScope(FirebaseCloudMessaging::FIREBASE_MESSAGING);
            $googleClient->useApplicationDefaultCredentials();
        } catch (\Exception $e) {
            throw new \Exception('Unable to init Google Client: ' . $e->getMessage());
        }

        return $googleClient;
    }

    private function initHttpClient(): GuzzleClient
    {
        return new GuzzleClient([
            'timeout' => 3,
        ]);
    }

    private function getAndroidConfig(?int $badge): AndroidConfig
    {
        $androidConfig = new AndroidConfig();
        $androidNtf = new AndroidNotification();
        $androidNtf->setDefaultSound(true);

        if ($badge) {
            $androidNtf->setNotificationCount($badge);
        }

        $androidConfig->setNotification($androidNtf);

        return $androidConfig;
    }

    private function getApnsConfig(?int $badge): ApnsConfig
    {
        $apnsConfig = new ApnsConfig();
        $payload = [
            'aps' => [
//                'alert' => [
//                    'title' => 'title',
//                    'body' => 'body',
//                ],
                'sound' => 'default',
            ],
        ];

        if ($badge) {
            $payload['aps']['badge'] = $badge;
        }

        $apnsConfig->setPayload($payload);

        return $apnsConfig;
    }

    private function createMessage(
        NotificationMobileDevice $mobileDevice,
        string                   $title,
        string                   $body,
        ?array                   $additionalData = [],
        ?int                     $badge = null,
    ): FCMMsg {
        $ntf = new FCMNtf();
        $ntf->setBody($body);
        $ntf->setTitle($title);
        $msg = new FCMMsg();
        $msg->setToken($mobileDevice->getDeviceToken());
        $mobileDevice->isIos()
            ? $msg->setApns($this->getApnsConfig($badge))
            : $msg->setAndroid($this->getAndroidConfig($badge));
        $msg->setNotification($ntf);

        if ($additionalData) {
            $msg->setData(array_map('strval', $additionalData));
        }

        return $msg;
    }

    public function __construct(
        private readonly string $googleAppConfig,
        private readonly LoggerInterface $logger,
        private ?GoogleClient $googleClient,
    ) {
        $this->googleClient = $this->initGoogleClient();
    }

    public function sendNotificationMsg(
        NotificationMobileDevice $mobileDevice,
        Message $message,
    ) {
        return $this->sendNotification($mobileDevice, $message->getBodySubject(), $message->getBodyMessage());
    }

    public function sendNotification(
        NotificationMobileDevice $mobileDevice,
        string                   $title,
        string                   $body,
        ?array                   $additionalData = [],
        ?int                     $badge = null,
    ) {
        try {
            $msg = $this->createMessage(
                $mobileDevice,
                $title,
                $body,
                $additionalData,
                $badge
            );
            $tokenData = $this->googleClient->fetchAccessTokenWithAssertion();
            $cloudMessaging = new FirebaseCloudMessaging($this->googleClient);
            $msgRequest = new FirebaseCloudMessaging\SendMessageRequest();
            $msgRequest->setMessage($msg);

            return $cloudMessaging->projects_messages->send(
                sprintf('projects/%s', self::FCM_PROJECT_ID),
                $msgRequest
            );
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e), [
                'NotificationMobileDevice.id' => $mobileDevice->getId(),
                'deviceToken' => $mobileDevice->getDeviceToken(),
                'title' => $title,
                'body' => $body,
            ]);

            return ['message' => $e->getMessage()];
        }
    }
}
