<?php

namespace App\Service\Vehicle;

use App\Entity\Asset;
use App\Entity\Depot;
use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\Notification\Event;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\RedisService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentService extends BaseService
{
    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'title' => 'title',
        'status' => 'commonDocumentStatus',
        'vehicleId' => 'vehicle.id',
        'driverId' => 'driver.id',
        'assetId' => 'asset.id',
        'regNo' => 'regNo',
        'documentType' => 'documentType',
        'team' => 'team',
        'teamId' => 'team.id',
        'fullName' => 'fullName',
        'groups' => 'vehicle.groups.id',
        'depot' => 'vehicle.depot.id',
    ];
    public const ELASTIC_RANGE_FIELDS = [
        'issueDate' => 'currentActiveRecord.issueDate',
        'expDate' => 'currentActiveRecord.expDate'
    ];
    public const ELASTIC_SCRIPT_FIELDS = [
        'remainingDays' => 'currentActiveRecord.expDate'
    ];

    protected $documentRepo;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransformedFinder $documentFinder,
        private readonly DocumentRecordService $documentRecordService,
        private readonly RedisService $dashboardCache,
        private readonly NotificationEventDispatcher $notificationDispatcher,
        private readonly ObjectPersister $documentObjectPersister,
        protected readonly TranslatorInterface $translator
    ) {
        $this->documentRepo = $em->getRepository(Document::class);
    }

    /**
     * @param int $id
     * @param User $currentUser
     * @return Document|null
     */
    public function getById(int $id, User $currentUser): ?Document
    {
        return $this->documentRepo->getDocumentById($id, $currentUser);
    }

    public function documentsList(array $params, $type, bool $paginated = true): array
    {
        $params['fields'] = Document::prepareListFields($params['fields'] ?? Document::DEFAULT_LISTING_DISPLAY_VALUES);
        $params = Document::handleStatusParams($params);
        $params['documentType'] = $type;

        $fields = $this->prepareElasticFields($params);

        if (isset($fields['script']['currentActiveRecord.expDate'])) {
            $fields['script'] = $this->calculateRemainingDays(
                'currentActiveRecord.expDate',
                $fields['script']['currentActiveRecord.expDate']
            );
        }

        $elastica = new ElasticSearch($this->documentFinder);

        return $elastica->find($fields, $fields['_source'], $paginated);
    }

    public function create(array $data, User $currentUser): Document
    {
        $this->validateCreateFields($data, $currentUser);

        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();
            if (isset($data['vehicleId'])) {
                $document = new Document($this->prepareFields($data));
                $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($currentUser, $data['vehicleId']);
                $document->setVehicle($vehicle);
            } elseif (isset($data['driverId'])) {
                $document = new Document($this->prepareFields($data));
                $driver = $this->em->getRepository(User::class)->find($data['driverId']);
                $document->setDriver($driver);
            } elseif (isset($data['assetId'])) {
                $document = new Document($this->prepareFields($data));
                $asset = $this->em->getRepository(Asset::class)->find($data['assetId']);
                $document->setAsset($asset);
            } else {
                throw new \Exception();
            }

            $document->setCreatedBy($currentUser);
            $this->em->persist($document);
            $this->em->flush();
            $connection->commit();

            if ($data['issueDate'] ?? null) {
                 $this->documentRecordService->create($document, $data, $currentUser);
                $document->refreshCurrentActiveRecord();
                $this->documentObjectPersister->replaceOne($document);
            }

            $this->em->refresh($document);

            $this->cleanCache($document->getTeam());

            return $document;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    protected function prepareFields(array $data)
    {
        if (isset($data['notifyBefore'])) {
            $data['notifyBefore'] = (int)$data['notifyBefore'];
        }

        return $data;
    }

    public function validateCreateFields(array $fields, User $currentUser): void
    {
        $errors = [];

        if ($fields['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($currentUser, $fields['vehicleId']);
            if (!$vehicle || !ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser)) {
                $errors['vehicleId'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        } elseif ($fields['driverId'] ?? null) {
            $driver = $this->em->getRepository(User::class)->find($fields['driverId']);
            if (!$driver || !ClientService::checkTeamAccess($driver->getTeam(), $currentUser)
                || !$driver->isDriverClientOrDualAccount()) {
                $errors['driverId'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        } elseif ($fields['assetId'] ?? null) {
            $asset = $this->em->getRepository(Asset::class)->find($fields['assetId']);
            if (!$asset || !ClientService::checkTeamAccess($asset->getTeam(), $currentUser)) {
                $errors['assetId'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        } else {
            $errors['vehicleId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            $errors['driverId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
            $errors['assetId'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if ($fields['notifyBefore'] ?? null) {
            if (!is_numeric($fields['notifyBefore'])) {
                $errors['notifyBefore'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            } elseif ($fields['notifyBefore'] != abs($fields['notifyBefore'])) {
                $errors['notifyBefore'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            } elseif ($fields['notifyBefore'] != (int)$fields['notifyBefore']) {
                $errors['notifyBefore'] = [
                    'wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')
                ];
            }
        }

        if (!($fields['title'] ?? null)) {
            $errors['title'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function edit(Document $document, array $data, User $currentUser): Document
    {
        $this->validateEditFields($data);

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $document->setAttributes($data);
            $document->setUpdatedAt(new \DateTime());
            $document->setUpdatedBy($currentUser);

            if ($data['issueDate'] ?? null) {
                $this->documentRecordService->create($document, $data, $currentUser);
                $document->refreshCurrentActiveRecord();
                $this->documentObjectPersister->replaceOne($document);
            }

            $document->getCurrentActiveRecord()?->setStatus(
                $this->documentRecordService->calculateStatus($document->getCurrentActiveRecord())
            );

            $this->em->flush();
            $connection->commit();

            $this->em->refresh($document);

            $this->cleanCache($document->getTeam());

            return $document;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    public function validateEditFields(array $fields): void
    {
        $errors = [];

        if (isset($fields['title']) && empty($fields['title'])) {
            $errors['title'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if ($fields['notifyBefore'] ?? null) {
            if (!is_numeric($fields['notifyBefore'])) {
                $errors['notifyBefore'] = [
                    'wrong_value' => $this->translator->trans(
                        'validation.errors.field.wrong_value'
                    )
                ];
            } elseif ($fields['notifyBefore'] != abs($fields['notifyBefore'])) {
                $errors['notifyBefore'] = [
                    'wrong_value' => $this->translator->trans(
                        'validation.errors.field.wrong_value'
                    )
                ];
            } elseif ($fields['notifyBefore'] != (int)$fields['notifyBefore']) {
                $errors['notifyBefore'] = [
                    'wrong_value' => $this->translator->trans(
                        'validation.errors.field.wrong_value'
                    )
                ];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function delete(Document $document, User $user): void
    {
        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();

            $document
                ->setStatus(Document::STATUS_DELETED)
                ->setUpdatedBy($user)
                ->setUpdatedAt(new \DateTime());

            $this->em->flush();

            if ($document->isVehicleDocument()) {
                $this->notificationDispatcher->dispatch(Event::DOCUMENT_DELETED, $document);
            } elseif ($document->isDriverDocument()) {
                $this->notificationDispatcher->dispatch(Event::DRIVER_DOCUMENT_DELETED, $document);
            } elseif ($document->isAssetDocument()) {
                $this->notificationDispatcher->dispatch(Event::ASSET_DOCUMENT_DELETED, $document);
            }

            $connection->commit();

            $this->cleanCache($document->getTeam());
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function getExpiredAndDueSoonStat(User $currentUser)
    {
        $redisKey = $this->getRedisKey($currentUser->getTeam());
        $redisData = $this->dashboardCache->getFromJson($redisKey);
        if ($redisData) {
            return $redisData;
        }

        $expiredCount = $this->em->getRepository(Document::class)
            ->getDocumentsCountWithStatus($currentUser, DocumentRecord::STATUS_EXPIRED);

        $dueSoonCount = $this->em->getRepository(Document::class)
            ->getDocumentsCountWithStatus($currentUser, DocumentRecord::STATUS_EXPIRE_SOON);

        $activeCount = $this->em->getRepository(Document::class)
            ->getDocumentsCountWithStatus($currentUser, DocumentRecord::STATUS_ACTIVE);

        $documents = $this->em->getRepository(Document::class)->getLastDocuments($currentUser, 3);

        $data = [
            'documents' => array_map(
                function ($item) {
                    return $item->toArray(array_merge(Document::DEFAULT_LISTING_DISPLAY_VALUES, ['vehicleRegNo']));
                },
                $documents
            ),
            DocumentRecord::STATUS_EXPIRED => [
                'count' => $expiredCount,
            ],
            DocumentRecord::STATUS_EXPIRE_SOON => [
                'count' => $dueSoonCount,
            ],
            DocumentRecord::STATUS_ACTIVE => [
                'count' => $activeCount,
            ]
        ];

        $this->dashboardCache->setToJsonTtl($redisKey, $data, 300);

        return $data;
    }

    /**
     * @param Team $team
     * @return string
     */
    public function getRedisKey(Team $team)
    {
        return 'expiredAndDueSoonStatDocuments-' . $team->getId();
    }

    /**
     * @param Team $team
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function cleanCache(Team $team)
    {
        $this->dashboardCache->deleteItem($this->getRedisKey($team));
    }

    /**
     * @param Document $document
     * @param User $user
     * @return Document
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function restore(Document $document, User $user): Document
    {
        $document->setStatus(Document::STATUS_ACTIVE)->setUpdatedBy($user)->setUpdatedAt(new \DateTime());

        $this->em->flush();
        $this->em->refresh($document);

        $this->cleanCache($document->getTeam());

        return $document;
    }

    public function archive(Document $document, User $user): Document
    {
        $document->setStatus(Document::STATUS_ARCHIVE)->setUpdatedBy($user)->setUpdatedAt(new \DateTime());

        $this->em->flush();
        $this->em->refresh($document);

        $this->cleanCache($document->getTeam());

        return $document;
    }

    public function prepareExportData($documents, $params, User $user)
    {
        return $this->translateEntityArrayForExport(
            $documents, $params['fields'] ?? Document::REPORT_DISPLAY_VALUES, Document::class, $user);
    }

    public function duplicate(
        Document $document,
        User $currentUser,
        array $vehicles = [],
        array $depots = [],
        array $groups = [],
        array $assets = []
    ): Document {
        if ($vehicles) {
            $vehicles = $this->em->getRepository(Vehicle::class)
                ->findBy(['id' => $vehicles, 'team' => $document->getTeam()]);

            foreach ($vehicles as $vehicle) {
                $this->createDocumentCopy($document, $currentUser, $vehicle);
            }
        }
        if ($depots) {
            $depots = $this->em->getRepository(Depot::class)
                ->findBy(['id' => $depots, 'team' => $document->getTeam()]);
            foreach ($depots as $depot) {
                $vehicles = $depot->getVehicles();

                foreach ($vehicles as $vehicle) {
                    $this->createDocumentCopy($document, $currentUser, $vehicle);
                }
            }
        }
        if ($groups) {
            $vehicles = $this->em->getRepository(VehicleGroup::class)
                ->getVehiclesByGroups($groups, $document->getTeam());

            foreach ($vehicles as $vehicle) {
                $this->createDocumentCopy($document, $currentUser, $vehicle);
            }
        }
        if ($assets) {
            $assets = $this->em->getRepository(Asset::class)
                ->findBy(['id' => $assets, 'team' => $document->getTeam()]);

            foreach ($assets as $asset) {
                $this->createDocumentCopy($document, $currentUser, null, $asset);
            }
        }

        $this->em->flush();

        return $document;
    }

    protected function createDocumentCopy(
        Document $document,
        User $currentUser,
        ?Vehicle $vehicle = null,
        ?Asset $asset = null
    ): void {
        if ($vehicle && $vehicle->getId() === $document->getVehicle()->getId()) {
            return;
        }
        if ($asset && $asset->getId() === $document->getAsset()->getId()) {
            return;
        }

        $copyDocument = clone $document;

        if ($vehicle) {
            $copyDocument->setVehicle($vehicle);
        } elseif ($asset) {
            $copyDocument->setAsset($asset);
        }

        $copyDocument->setCreatedBy($currentUser);
        $copyDocument->setCreatedAt(new \DateTime());
        $copyDocument->setUpdatedBy(null);
        $copyDocument->setUpdatedAt(null);
        $this->em->persist($copyDocument);
    }
}
