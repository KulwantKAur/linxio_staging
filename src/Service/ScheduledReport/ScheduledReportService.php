<?php

namespace App\Service\ScheduledReport;

use App\Entity\Role;
use App\Entity\ScheduledReport;
use App\Entity\ScheduledReportRecipients;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Report\ReportService;
use App\Util\StringHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScheduledReportService extends BaseService
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $em;
    private ElasticSearch $reportFinder;
    private ReportService $reportService;

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $reportFinder,
        ReportService $reportService
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->reportFinder = new ElasticSearch($reportFinder);
        $this->reportService = $reportService;
    }

    public const ELASTIC_FULL_SEARCH_FIELDS = [
        'defaultlabel',
        'regNo',
        'model',
        'depot.name',
        'groups.name',
        'driverName',
        'areas.name'
    ];
    public const ELASTIC_NESTED_FIELDS = [
    ];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'type' => 'type',
        'frequency' => 'intervalType',
        'interval' => 'periodDays',
        'status' => 'status',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'teamId' => 'teamId',
        'eventId' => 'eventId'
    ];
    public const ELASTIC_RANGE_FIELDS = [];

    public function create(array $data, User $currentUser)
    {
        $this->validateCreateData($data);
        if ($data['timezone'] ?? null) {
            $data['timezone'] = $this->em->getRepository(TimeZone::class)->find($data['timezone']);
        }
        $data['team'] = $currentUser->getTeam();
        $data['createdBy'] = $currentUser;

        $scheduledReport = new ScheduledReport($data);
        $scheduledReport->setRecipient($this->handleRecipients($data));

        $this->em->persist($scheduledReport);
        $this->em->flush();

        return $scheduledReport;
    }

    public function edit(ScheduledReport $scheduledReport, array $data, User $currentUser)
    {
        $this->validateEditData($data);

        if ($data['timezone'] ?? null) {
            $data['timezone'] = $this->em->getRepository(TimeZone::class)->find($data['timezone']);
        }
        $scheduledReport->setAttributes($data);
        $scheduledReport->setRecipient($this->handleRecipients($data, $scheduledReport->getRecipient()));
        $scheduledReport->setUpdatedAt(new \DateTime());
        $scheduledReport->setUpdatedBy($currentUser);

        $this->em->flush();
        $this->em->refresh($scheduledReport);

        return $scheduledReport;
    }

    public function delete(ScheduledReport $scheduledReport)
    {
        $scheduledReport->setStatus(ScheduledReport::STATUS_DELETED);
        $this->em->flush();
    }

    public function restore(ScheduledReport $scheduledReport)
    {
        $scheduledReport->setStatus(ScheduledReport::STATUS_ACTIVE);
        $this->em->flush();
    }

    public function getById($id, User $currentUser): ?ScheduledReport
    {
        if ($currentUser->isInAdminTeam()) {
            return $this->em->getRepository(ScheduledReport::class)->find($id);
        } else {
            return $this->em->getRepository(ScheduledReport::class)->findOneBy([
                'id' => $id,
                'team' => $currentUser->getTeam()
            ]);
        }
    }

    private function validateCreateData(array $data)
    {
        $errors = [];
        if (!isset($data['type']) || is_null($data['type'])) {
            $errors['type'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (!isset($data['name']) || is_null($data['name'])) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (!isset($data['interval']) || is_null($data['interval'])) {
            $errors['interval'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (!isset($data['format']) || is_null($data['format'])) {
            $errors['format'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (isset($data['recipients']['type']) || isset($data['recipients']['value'])) {
            if (!isset($data['recipients']['type']) || is_null($data['recipients']['type'])) {
                $errors['recipients']['type'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            if (!isset($data['recipients']['value']) || is_null($data['recipients']['value'])) {
                $errors['recipients']['value'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
        } elseif (!isset($data['recipients']['emails'])) {
            $errors['recipients']['emails'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    private function validateEditData(array $data)
    {
        $errors = [];
        if (isset($data['type']) && (is_null($data['type']) || empty($data['type']))) {
            $errors['type'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (isset($data['name']) && (is_null($data['name']) || empty($data['name']))) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (isset($data['interval']) && (is_null($data['interval']) || empty($data['interval']))) {
            $errors['interval'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (isset($data['format']) && (is_null($data['format']) || empty($data['format']))) {
            $errors['format'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (isset($data['recipients']['type']) || isset($data['recipients']['value'])) {
            if (!isset($data['recipients']['type']) || !isset($data['recipients']['value'])) {
                $errors['recipients'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            if (isset($data['recipients']['value']) && empty($data['recipients']['value'])
                && (!isset($data['recipients']['emails']) || empty($data['recipients']['emails']))) {
                $errors['recipients'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            if (isset($data['recipients']['value']) && (is_null($data['recipients']['value']))) {
                $errors['recipients']['value'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
        }
        if (isset($data['recipients']['emails']) && !is_array($data['recipients']['emails'])) {
            $errors['recipients']['emails'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function handleRecipients(array $data, ?ScheduledReportRecipients $recipient = null)
    {
        if (isset($data['recipients'])) {
            $recipient = $recipient ?? new ScheduledReportRecipients();
            $recipient->setType($data['recipients']['type'] ?? null);
            $recipient->setValue($data['recipients']['value'] ?? null);
            if (isset($data['recipients']['emails']) && $data['recipients']['emails']) {
                $recipient->setCustom(['emails' => $data['recipients']['emails']]);
            } else {
                $recipient->setCustom(null);
            }
            $this->em->persist($recipient);
        }

        return $recipient;
    }

    private function prepareScheduledReportParams(ScheduledReport $scheduledReport, ?\DateTime $dateTime = null)
    {
        return $scheduledReport->getStartAndEndDate($dateTime);
    }

    public function getReportData(ScheduledReport $scheduledReport, ?\DateTime $dateTime = null)
    {
        $params = array_merge(
            $this->prepareScheduledReportParams($scheduledReport, $dateTime),
            $scheduledReport->getParams()
        );
        $format = $scheduledReport->getFormat();
        $user = $scheduledReport->getCreatedBy();

        //hack for tz sending scheduler report
        if ($scheduledReport->getTimezoneEntity()) {
            $user->setTimezone($scheduledReport->getTimezoneEntity());
        }

        return $this->reportService->init($scheduledReport->getType())->getReport($format, $params, $user);
    }

    public function getRecipientsEmails(ScheduledReport $scheduledReport)
    {
        $recipient = $scheduledReport->getRecipient();
        $emails = $recipient->getEmails();
        switch ($recipient->getType()) {
            case ScheduledReportRecipients::TYPE_USER:
                $users = $this->em->getRepository(User::class)
                    ->findBy([
                        'id' => $recipient->getValue(),
                        'team' => $scheduledReport->getCreatedBy()->getTeam(),
                        'status' => [User::STATUS_ACTIVE, User::STATUS_NEW]
                    ]);
                foreach ($users as $user) {
                    $emails[] = $user->getEmail();
                }
                break;
            case ScheduledReportRecipients::TYPE_USER_GROUP:
                $userGroups = $this->em->getRepository(UserGroup::class)
                    ->findBy(['id' => $recipient->getValue(), 'team' => $scheduledReport->getCreatedBy()->getTeam()]);
                foreach ($userGroups as $userGroup) {
                    foreach ($userGroup->getUsersForEmail() as $user) {
                        $emails[] = $user->getEmail();
                    }
                }
                break;
            case ScheduledReportRecipients::TYPE_ROLE:
                $roles = $this->em->getRepository(Role::class)->findBy(['id' => $recipient->getValue()]);
                $users = $this->em->getRepository(User::class)
                    ->findBy([
                        'role' => $roles,
                        'team' => $scheduledReport->getCreatedBy()->getTeam(),
                        'status' => [User::STATUS_ACTIVE, User::STATUS_NEW]
                    ]);
                foreach ($users as $user) {
                    $emails[] = $user->getEmail();
                }
                break;
        }

        return $emails;
    }

    public function list(array $params, User $user, bool $paginated = true)
    {
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }

        $params = self::handleStatusParams($params);
        $fields = $this->prepareElasticFields($params);

        return $this->reportFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    public static function handleStatusParams(array $params)
    {
        if (isset($params['status'])) {
            $params['status'] = $params['status'] === ScheduledReport::STATUS_ALL ? ScheduledReport::ALLOWED_STATUSES : $params['status'];
        } else {
            $params['status'] = ScheduledReport::LIST_STATUSES;
        }

        if (isset($params['showDeleted']) && StringHelper::stringToBool($params['showDeleted'])) {
            if (is_array($params['status'])) {
                $params['status'][] = ScheduledReport::STATUS_DELETED;
            } else {
                $status = $params['status'];
                $params['status'] = [$status, ScheduledReport::STATUS_DELETED];
            }
        } elseif (
            is_array($params['status']) && ($key = array_search(ScheduledReport::STATUS_DELETED,
                $params['status'])) !== false
        ) {
            unset($params['status'][$key]);
        } elseif (!is_array($params['status']) && $params['status'] === ScheduledReport::STATUS_DELETED) {
            $params['status'] = '';
        }

        return $params;
    }
}