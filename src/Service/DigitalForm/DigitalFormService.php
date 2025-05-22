<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm;

use App\Entity\DigitalForm;
use App\Entity\DigitalFormAnswer;
use App\Entity\DigitalFormStep;
use App\Entity\DriverHistory;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Events\DigitalForm\DigitalFormEvent;
use App\Exceptions\DigitalFormStepFactoryException;
use App\Exceptions\ValidationException;
use App\Fixtures\DigitalForm\DefaultDigitalFormFixture;
use App\Repository\DigitalFormRepository;
use App\Service\DigitalForm\Entity\Condition;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class DigitalFormService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var TranslatorInterface */
    private $translator;

    /** @var DigitalFormStepFactory */
    private $stepFactory;

    /** @var DigitalFormScheduleService */
    private $scheduleService;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    private ObjectPersisterInterface $objectPersister;


    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        DigitalFormStepFactory $stepFactory,
        DigitalFormScheduleService $scheduleService,
        ObjectPersisterInterface $persister,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->stepFactory = $stepFactory;
        $this->scheduleService = $scheduleService;
        $this->objectPersister = $persister;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getActiveForms(Team $team): array
    {
        /** @var DigitalFormRepository */
        $repo = $this->em->getRepository(DigitalForm::class);

        return $repo->getActiveForms($team);
    }

    public function getDigitalFormById(int $id, Team $team): ?DigitalForm
    {
        /** @var DigitalFormRepository */
        $repo = $this->em->getRepository(DigitalForm::class);

        return $repo->getById($id, $team);
    }

    public function createForm(Request $request, User $user, ?DigitalForm $form = null): DigitalForm
    {
        $type = $request->request->get('type');
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $status = ($request->request->get('isUnavailable') === 'true')
            ? DigitalForm::STATUS_UNAVAILABLE : DigitalForm::STATUS_ACTIVE;
        $inspectionPeriod = $request->request->get('inspectionPeriod');
        $emails = $request->request->all('emails') ?? [];
        $this->validateForm($user->getTeamId(), $type, $title, $status, $inspectionPeriod, $form);

        $steps = $request->request->all('steps') ?? [];
        $this->validateSteps($steps);

        try {
            $this->em->getConnection()->beginTransaction();

            $form = new DigitalForm();
            $form->setType($type);
            $form->setTitle($title);
            $form->setTeam($user->getTeam());
            $form->setCreatedBy($user);
            $form->setActive(true);
            $form->setStatus($status);
            $form->setDescription($description);
            $form->setInspectionPeriod($inspectionPeriod);
            $form->setEmails($emails);
            $this->em->persist($form);

            foreach ($steps as $stepData) {
                $step = new DigitalFormStep();
                $step->setTitle((string)$stepData['title']);
                $step->setDescription((string)$stepData['description'] ?? '');
                $step->setStepOrder((int)$stepData['order']);
                $step->setDigitalForm($form);
                $step->setCondition($this->getCondition($stepData['condition'] ?? null));
                $step->setOptions($stepData['options'] ?? []);
                $this->em->persist($step);
            }

            // create schedule entities
            $this->scheduleService->createSchedule($request, $user, $form);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(new DigitalFormEvent($form), DigitalFormEvent::FORM_CREATE);
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function editForm(Request $request, User $user, DigitalForm $form): DigitalForm
    {
        try {
            $this->em->getConnection()->beginTransaction();

            // create new form and mark it like child
            $newForm = $this->createForm($request, $user, $form);
            $newForm->setOldId($form->getId());
            $this->em->persist($newForm);

            // check case for common forms (available for all clients)
            if ($form->getTeam() !== null) {
                // mark origin form like inactive
                $form->setActive(false);
                $this->em->persist($form);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();

            // update old form in elasticsearch
            $this->updateElasticsearchData($form);

            $this->eventDispatcher->dispatch(new DigitalFormEvent($form), DigitalFormEvent::FORM_EDIT);
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        return $newForm;
    }

    /**
     * @throws \Exception
     */
    public function createDefaultForm(User $currentUser, Team $team): void
    {
        /** @var DigitalFormRepository */
        $repo = $this->em->getRepository(DigitalForm::class);

        // if client have inactive form - don't create default
        $rows = $repo->getFormsByTeam($team);
        if (count($rows) > 0) {
            return;
        }

        // try to find default form
        /** @var DigitalForm $defaultForm */
        $defaultForm = $repo->findOneBy(['title' => DefaultDigitalFormFixture::FORM_NAME, 'team' => null, 'active' => true], ['id' => 'ASC']);
        if ($defaultForm === null) {
            return;
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $form = new DigitalForm();
            $form->setType($defaultForm->getType());
            $form->setTitle($defaultForm->getTitle());
            $form->setTeam($team);
            $form->setCreatedBy($currentUser);
            $form->setActive(true);
            $form->setStatus($defaultForm->getStatus());
            $form->setInspectionPeriod($defaultForm->getInspectionPeriod());
            $this->em->persist($form);

            foreach ($defaultForm->getDigitalFormSteps() as $step) {
                $item = new DigitalFormStep();
                $item->setTitle($step->getTitle());
                $item->setDescription($step->getDescription());
                $item->setStepOrder($step->getStepOrder());
                $item->setDigitalForm($form);

                if (!empty($step->getCondition())) {
                    $item->setCondition($step->getCondition());
                }
                if (!empty($step->getOptions())) {
                    $item->setOptions($step->getOptions());
                }

                $this->em->persist($item);
            }

            // create schedule entities
            $this->scheduleService->createDefaultSchedule($currentUser, $defaultForm, $form);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(new DigitalFormEvent($form), DigitalFormEvent::FORM_CREATE);
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param DigitalForm $form
     * @throws ValidationException
     */
    public function deleteForm(DigitalForm $form): void
    {
        $this->validateForm(
            $form->getTeam()->getId(),
            $form->getType(),
            $form->getTitle(),
            DigitalForm::STATUS_DELETED,
            $form->getInspectionPeriod(),
            $form
        );

        $this->updateStatusForm($form, DigitalForm::STATUS_DELETED);

        $this->eventDispatcher->dispatch(new DigitalFormEvent($form), DigitalFormEvent::FORM_DELETE);
    }

    /**
     * @param DigitalForm $form
     * @throws ValidationException
     */
    public function restoreForm(DigitalForm $form): void
    {
        $this->validateForm(
            $form->getTeam()->getId(),
            $form->getType(),
            $form->getTitle(),
            DigitalForm::STATUS_UNAVAILABLE,
            $form->getInspectionPeriod(),
            $form
        );

        $this->updateStatusForm($form, DigitalForm::STATUS_ACTIVE);

        $this->eventDispatcher->dispatch(new DigitalFormEvent($form), DigitalFormEvent::FORM_RESTORE);
    }

    /**
     * @param int $teamId
     * @param string|null $type
     * @param string|null $title
     * @param string|null $status
     * @param string|null $inspectionPeriod
     * @param DigitalForm|null $form
     *
     * @throws ValidationException
     */
    private function validateForm(
        int $teamId,
        string $type = null,
        string $title = null,
        string $status = null,
        ?string $inspectionPeriod = null,
        ?DigitalForm $form = null
    ): void {

        $errors = [];
        if (empty($title)) {
            $errors['title'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (empty($type)) {
            $errors['type'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (!in_array($type, DigitalForm::VALID_TYPES)) {
            $errors['type'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if (!in_array($status, DigitalForm::STATUS_VALID_TYPES)) {
            $errors['status'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if (!in_array($inspectionPeriod, DigitalForm::INSPECTION_PERIOD_VALID_TYPES)) {
            $errors['inspectionPeriod'] = [
                'required' => $this->translator->trans('validation.errors.field.wrong_value')
            ];
        }

        //only 1 everytime form for team
        if ($inspectionPeriod === DigitalForm::INSPECTION_PERIOD_EVERY_TIME) {
            $query = $this->em->createQueryBuilder()
                ->select('df')
                ->from(DigitalForm::class, 'df')
                ->andWhere('df.team = :team')
                ->andWhere('df.active = :active')
                ->andWhere('df.status = :status')
                ->andWhere('df.inspectionPeriod = :inspectionPeriod')
                ->setParameter('active', true)
                ->setParameter('status', DigitalForm::STATUS_ACTIVE)
                ->setParameter('team', $teamId)
                ->setParameter('inspectionPeriod', $inspectionPeriod);

            if (!empty($form)) {
                $query->andWhere('df.id != :formId')
                    ->setParameter('formId', $form->getId());
            }

            $forms = $query->getQuery()->getResult();

            if (!empty($forms)) {
                if (in_array($status, [DigitalForm::STATUS_ACTIVE])) {
                    $errors['inspectionPeriod'] = [
                        'required' => $this->translator->trans('validation.errors.field.only_one_every_time_form')
                    ];
                }

                if (in_array($status, [DigitalForm::STATUS_UNAVAILABLE])) {
                    $errors['inspectionPeriod'] = [
                        'required' => $this->translator->trans('validation.errors.field.only_one_active_every_time_form')
                    ];
                }
            }

            if (($status === DigitalForm::STATUS_DELETED)
                && empty($forms)
            ) {
                $errors['inspectionPeriod'] = [
                    'required' => $this->translator->trans('validation.errors.field.last_every_time_form')
                ];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateSteps(array &$steps): void
    {
        $errors = [];
        foreach ($steps as $key => &$step) {
            if (empty($step['title'])) {
                $errors['title-' . $key] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            if (empty($step['order'])) {
                $errors['order-' . $key] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            if (!empty($step['condition'])) {
                $condition = $step['condition'];
                if (empty($condition['questionId'])) {
                    $errors['condition-' . $key]['questionId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
                }
                if (empty($condition['operator'])) {
                    $errors['condition-' . $key]['operator'] = ['required' => $this->translator->trans('validation.errors.field.required')];
                }
                if (empty($condition['value'])) {
                    $errors['condition-' . $key]['value'] = ['required' => $this->translator->trans('validation.errors.field.required')];
                }
            }
            if (empty($step['options'])) {
                $errors['options-' . $key] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
            try {
                $options = $step['options'] ?? ['type' => null];
                $stepObj = $this->stepFactory->create($options['type'] ?? null);

                $stepObj->fromArray($options);
                $step['options'] = $stepObj->jsonSerialize();
            } catch (DigitalFormStepFactoryException $e) {
                $errors['options-' . $key] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    private function getCondition(array $condition = null): ?Condition
    {
        if ($condition === null) {
            return null;
        }

        return new Condition($condition['questionId'], $condition['operator'], $condition['value']);
    }

    /**
     * @param DigitalForm $form
     * @param string $status
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function updateStatusForm(DigitalForm $form, string $status): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $form->setStatus($status);
            $this->em->persist($form);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->updateElasticsearchData($form);
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    private function updateElasticsearchData(DigitalForm $form): void
    {
        try {
            $rows = $this->scheduleService->getSchedulesByFormId($form->getId());
            $this->objectPersister->replaceMany($rows);
        } catch (\Exception $e) {
            // just skip elasticsearch errors
        }
    }

    /**
     * get any uncompleted form
     *
     * @param Vehicle $vehicle
     * @param User $user
     * @param string $dateFrom
     * @return DigitalForm|null
     */
    public function checkInspectionFormComplete(Vehicle $vehicle, User $user, string $dateFrom)
    {
        $form = null;

        if ($dateFrom) {
            $withInspectionPeriods = [
//                DigitalForm::INSPECTION_PERIOD_SHOW_ALWAYS,
                DigitalForm::INSPECTION_PERIOD_EVERY_TIME,
                DigitalForm::INSPECTION_PERIOD_ONCE_PER_DAY,
                DigitalForm::INSPECTION_PERIOD_ONCE_PER_WEEK,
                DigitalForm::INSPECTION_PERIOD_ONCE_PER_MONTH
            ];

            //LIN 1584 - if user has a few Every Time forms, then check answer only for 1 and skip all other
            $lastAnsweredEveryTimeForm = $this->em->getRepository(DigitalForm::class)
                ->getLastAnsweredForm(
                    $user,
                    (new Carbon($dateFrom))->setTimezone($user->getTimezone())->startOfDay()->setTimezone('UTC'),
                    DigitalForm::TYPE_INSPECTION,
                    DigitalForm::INSPECTION_PERIOD_EVERY_TIME
                );

            if ($lastAnsweredEveryTimeForm) {
                /** @var DigitalFormAnswer $latestDigitalForm */
                $latestDigitalForm = $this->em->getRepository(DigitalFormAnswer::class)
                    ->getLatestDigitalFormAnswer(
                        $vehicle,
                        DigitalForm::TYPE_INSPECTION,
                        $user,
                        $lastAnsweredEveryTimeForm
                    );

                /** @var DriverHistory $prevDriverHistory */
                $prevDriverHistory = $this->em->getRepository(DriverHistory::class)
                    ->findLastHistoryByVehicle($vehicle);

                // After reassigning driver to vehicle, the form was not completed - send form for event
                if (is_null($latestDigitalForm)
                    || ($latestDigitalForm->getDigitalForm()->getId() === $lastAnsweredEveryTimeForm->getId()
                        && $latestDigitalForm->getCreatedAt() < $prevDriverHistory->getStartDate())
                ) {
                    //have to fill form again
                    //$withInspectionPeriods includes all periods
                } else {
                    //LIN 1584 - we have answer for 1 Every Time form and can skip all other with the same period
                    $withInspectionPeriods = [
//                        DigitalForm::INSPECTION_PERIOD_SHOW_ALWAYS,
                        DigitalForm::INSPECTION_PERIOD_ONCE_PER_DAY,
                        DigitalForm::INSPECTION_PERIOD_ONCE_PER_WEEK,
                        DigitalForm::INSPECTION_PERIOD_ONCE_PER_MONTH
                    ];
                }
            }

            $form = $this->scheduleService->getInspectionForm($user, $withInspectionPeriods);
        }

        return $form;
    }
}
