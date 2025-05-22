<?php

namespace App\Service\ReminderCategory;

use App\Entity\Reminder;
use App\Entity\ReminderCategory;
use App\Entity\Team;
use App\Entity\User;
use App\Events\ReminderCategory\ReminderCategoryCreatedEvent;
use App\Events\ReminderCategory\ReminderCategoryDeletedEvent;
use App\Events\ReminderCategory\ReminderCategoryUpdatedEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Validation\ValidationService;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Notification\EventDispatcher;

class ReminderCategoryService extends BaseService
{
    protected $translator;
    private $em;
    private $reminderCategoryFinder;
    private $eventDispatcher;
    private $validationService;
    private $notificationDispatcher;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'name' => 'name',
        'status' => 'status',
        'teamId' => 'team.id',
        'reminders' => 'reminders.id',
        'order' => 'order'
    ];

    /**
     * ReminderService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $reminderCategoryFinder
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidationService $validationService
     * @param EventDispatcher $notificationDispatcher
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $reminderCategoryFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidationService $validationService,
        EventDispatcher $notificationDispatcher
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->reminderCategoryFinder = new ElasticSearch($reminderCategoryFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->validationService = $validationService;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function list(array $params, User $user, bool $paginated = true)
    {
        if (isset($params['status'])) {
            $params['status'] = $params['status'] === ReminderCategory::STATUS_ALL ? ReminderCategory::STATUSES : $params['status'];
        } else {
            $params['status'] = ReminderCategory::LIST_STATUSES;
        }

        $fields = $this->prepareElasticFields($params);

        return $this->reminderCategoryFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }


    /**
     * @param int $id
     * @return object|null
     */
    public function getById(int $id)
    {
        return $this->em->getRepository(ReminderCategory::class)
            ->findOneBy(['id' => $id, 'status' => ReminderCategory::STATUS_ACTIVE]);
    }


    /**
     * @param array $fields
     * @param User $currentUser
     * @param ReminderCategory|null $reminderCategory
     */
    private function validateReminderCategoryFields(
        array $fields,
        User $currentUser,
        ?ReminderCategory $reminderCategory = null
    ) {
        $errors = [];

        if (!($fields['name'] ?? null)) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if ($fields['name']) {
            $reminderCategoryEntity = $this->em->getRepository(ReminderCategory::class)
                ->findUniqueCategory($fields['name']);

            if ($reminderCategoryEntity && (!$reminderCategory || $reminderCategoryEntity->getId() !== $reminderCategory->getId())) {
                $errors['name'] = ['required' => $this->translator->trans('entities.reminder_category.name')];
            }
        }

        if ($fields['status'] ?? null) {
            if (!in_array($fields['status'], ReminderCategory::STATUSES)) {
                $errors['status'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return ReminderCategory
     * @throws \Exception
     */
    public function create(array $data, User $currentUser)
    {
        $this->validateReminderCategoryFields($data, $currentUser);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $category = new ReminderCategory($data);
            $category->setCreatedBy($currentUser);

            $this->em->persist($category);
            $this->em->flush();

            $connection->commit();
            $this->em->refresh($category);

            $this->eventDispatcher->dispatch(
                new ReminderCategoryCreatedEvent($category),
                ReminderCategoryCreatedEvent::NAME
            );

            return $category;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param array $data
     * @param ReminderCategory $category
     * @param User $currentUser
     * @return ReminderCategory
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit(array $data, ReminderCategory $category, User $currentUser)
    {
        $this->validateReminderCategoryFields($data, $currentUser, $category);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            $category->setName($data['name']);
            $category->setStatus($data['status'] ?? $category->getStatus());
            $category->setUpdatedBy($currentUser);
            $category->setUpdatedAt(new \DateTime());

            $this->em->flush();

            $connection->commit();
            $this->em->refresh($category);

            $this->eventDispatcher->dispatch(
                new ReminderCategoryUpdatedEvent($category),
                ReminderCategoryUpdatedEvent::NAME
            );

            return $category;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param ReminderCategory $reminderCategory
     * @return ReminderCategory
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(ReminderCategory $reminderCategory)
    {
        $reminderCategory->setStatus(ReminderCategory::STATUS_DELETED);
        $reminderCategory->removeAllReminders();

        $this->em->flush();

        $this->eventDispatcher->dispatch(
            new ReminderCategoryDeletedEvent($reminderCategory),
            ReminderCategoryDeletedEvent::NAME
        );

        return $reminderCategory;
    }

    public function getReminderCategoryExportData($params, User $user, $paginated = false): array
    {
        $reminderCategories = $this->list($params, $user, $paginated);

        return $this->translateEntityArrayForExport($reminderCategories, $params['fields']);
    }

    public function changeOrder(array $data)
    {
        foreach ($data as $item) {
            $this->em->getReference(ReminderCategory::class, $item['id'])->setOrder($item['order']);
        }
        $this->em->flush();
    }
}