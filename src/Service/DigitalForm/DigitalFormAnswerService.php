<?php

namespace App\Service\DigitalForm;

use App\Entity\BaseEntity;
use App\Entity\DigitalForm;
use App\Entity\DigitalFormAnswer;
use App\Entity\DigitalFormAnswerStep;
use App\Entity\File;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Events\DigitalForm\DigitalFormAnswerEvent;
use App\Mailer\MailSender;
use App\Mailer\Render\TwigEmailRender;
use App\Repository\UserGroupRepository;
use App\Service\BaseService;
use App\Service\DigitalForm\Entity\Answer;
use App\Service\File\LocalFileService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\PdfService;
use App\Service\Vehicle\VehicleServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class DigitalFormAnswerService extends BaseService
{
    /** @inheritDoc */
    public const ELASTIC_SIMPLE_FIELDS = [
        'active' => 'digitalForm.active',
        'duration' => 'duration',
        'formId' => 'digitalForm.id',
        'id' => 'id',
        'isPass' => 'isPass',
        'status' => 'digitalForm.status',
        'teamId' => 'user.team.id',
        'title' => 'digitalForm.title',
        'type' => 'digitalForm.type',
        'userFullName' => 'user.fullName',
        'userId' => 'user.id',
        'vehicleDefaultLabel' => 'vehicle.defaultLabel',
        'vehicleId' => 'vehicle.id',
        'vehicleIds' => 'vehicle.id',
        'vehicleRegNo' => 'vehicle.regNo',
        'vehicleTeamId' => 'vehicle.team.id',
        'frequency' => 'digitalForm.inspectionPeriod',
    ];

    /** @inheritDoc */
    public const ELASTIC_RANGE_FIELDS = [
        'date' => 'createdAt',
    ];

    /** @var EntityManagerInterface */
    private $em;

    /** @var LocalFileService */
    private $fileService;

    /** @var ElasticSearchVehicleInspection */
    private $elasticSearch;

    /** @var LoggerInterface */
    private $logger;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    private $notificationDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        LocalFileService $fileService,
        ElasticSearchVehicleInspection $elasticSearch,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        private readonly MailSender $mailSender,
        private readonly PdfService $pdfService,
        private readonly TwigEmailRender $emailRender
    ) {
        $this->em = $em;
        $this->fileService = $fileService;
        $this->elasticSearch = $elasticSearch;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    public function getDigitalFormAnswerById(int $id): ?DigitalFormAnswer
    {
        return $this->em->getRepository(DigitalFormAnswer::class)->getById($id);
    }

    /**
     * @throws \Exception
     */
    public function createAnswer(DigitalForm $form, User $user, Answer $answer, int $vehicleId = 0): DigitalFormAnswer
    {
        // if user has not a vehicle - try to get it from form params
        $vehicle = $user->getVehicle();
        if ($vehicleId !== 0) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $answerEntity = new DigitalFormAnswer();
            $answerEntity->setDigitalForm($form);
            $answerEntity->setUser($user);
            $answerEntity->setVehicle($vehicle);
            $this->em->persist($answerEntity);

            foreach ($answer->getData() as $data) {
                $answerStep = new DigitalFormAnswerStep();
                $answerStep->setDigitalFormStep($data['step']);
                $answerStep->setDigitalFormAnswer($answerEntity);
                $answerStep->setDuration((int)($data['data']['duration'] ?? 0));

                // `isPass` flag can be set only for specific types
                $options = $data['step']->getOptions();

                if ($options['type'] === DigitalFormStepFactory::TYPE_LIST_SINGLE && is_array($options['failIndexes'])) {
                    $answerStep->setIsPass(!in_array($data['data']['value'], $options['failIndexes']));
                }

                if ($options['type'] === DigitalFormStepFactory::TYPE_LIST_MULTI && is_array($options['failIndexes'])) {
                    //TODO remove hack for mobile bug
                    if (is_string($data['data']['value'])) {
                        $answerStep->setIsPass(!in_array($data['data']['value'], $options['failIndexes']));
                    } elseif (is_array($data['data']['value'])) {
                        $answerStep->setIsPass(!array_intersect($data['data']['value'], $options['failIndexes']));
                    }
                }

                if (!empty($data['data']['additionalNote'])) {
                    $answerStep->setAdditionalNote(trim($data['data']['additionalNote']));
                }

                if (!empty($data['data']['additionalFile'])
                    && ($data['data']['additionalFile'] instanceof UploadedFile)) {
                    $fileEntity = $this->handleFileAnswer($user, $data['data']['additionalFile']);
                    $answerStep->setAdditionalFile($fileEntity);
                }

                if ($data['data']['value'] instanceof UploadedFile) {
                    $fileEntity = $this->handleFileAnswer($user, $data['data']['value']);
                    $answerStep->setFile($fileEntity);
                } else {
                    $answerStep->setValue($data['data']['value']);
                }

                $this->em->persist($answerStep);

                $answerEntity->addDigitalFormAnswerStep($answerStep);
            }
            $this->em->flush();
            $this->em->getConnection()->commit();

            if (!$answerEntity->getIsPass()) {
                $this->notificationDispatcher->dispatch(Event::DIGITAL_FORM_WITH_FAIL, $answerEntity);
            }
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            $this->logger->error('Create answer error: ' . $e->getMessage());
            throw $e;
        }

        $this->eventDispatcher->dispatch(
            new DigitalFormAnswerEvent($answerEntity), DigitalFormAnswerEvent::ANSWER_CREATED
        );

        $this->sendDigitalFormAnswer($answerEntity);

        return $answerEntity;
    }

    /**
     * @throws \Exception
     */
    public function getReportVehicleInspection(User $user, array $params, $paginated = true): ?array
    {
        try {
            $params['type'] = DigitalForm::TYPE_INSPECTION;
            $params['teamId'] = $user->getTeam()->getId();
            if ($user->needToCheckUserGroup()) {
                /** @var UserGroupRepository */
                $repo = $this->em->getRepository(UserGroup::class);
                $params['vehicleIds'] = $repo->getUserVehiclesIdFromUserGroup($user);
            }

            $params = VehicleServiceHelper::handleDriverVehicleParams($params, $this->em, $user, false, true);
            $params['fields'] = [
                'id',
                'duration',
                'createdAt',
                'isPass',
            ];
            if (isset($params['startDate'])) {
                $params['date']['gte'] = $params['startDate'];
            }
            if (isset($params['endDate'])) {
                $params['date']['lte'] = $params['endDate'];
            }

            $fields = $this->prepareElasticFields($params);

            return $this->elasticSearch->find($fields, ['isPass', 'duration', 'vehicle', 'form', 'user'], $paginated);
        } catch (\Exception $e) {
            $this->logger->error('Get vehicle report error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handleFileAnswer(User $user, UploadedFile $file): File
    {
        return $this->fileService->uploadDigitalFormFile($file, $user);
    }

    public function sendDigitalFormAnswer(DigitalFormAnswer $digitalFormAnswer)
    {
        if (!$digitalFormAnswer->getDigitalForm()->getEmails()) {
            return;
        }

        $pdf = $this->pdfService->getDigitalFormAnswerPdf($digitalFormAnswer);
        $date = $digitalFormAnswer->getDate()->format(BaseEntity::EXPORT_DATE_FORMAT);
        $regno = $digitalFormAnswer->getVehicle()->getRegNo();

        $pdfAttachment = MailSender::getPdfAttachment(
            $pdf, $regno . ' - ' . $digitalFormAnswer->getDigitalForm()->getTitle() . '.pdf'
        );

        $renderedEmail = $this->emailRender->render(
            'digitalForm/answer-email.html.twig',
            [
                'subject' => $digitalFormAnswer->getDigitalForm()->getTitle() . ' - ' . $regno,
                'title' => $digitalFormAnswer->getDigitalForm()->getTitle(),
                'date' => $date,
                'regno' => $regno,
                'user' => $digitalFormAnswer->getUser()->getFullName(),
                'result' => $digitalFormAnswer->getStatusRatio(),
                'isPass' => $digitalFormAnswer->getIsPass()
            ]
        );
        $this->mailSender->sendEmail(
            $digitalFormAnswer->getDigitalForm()->getEmails(),
            $renderedEmail, null, [], null, [$pdfAttachment]
        );
    }
}
