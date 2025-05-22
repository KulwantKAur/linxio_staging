<?php

namespace App\Service\Vehicle;

use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\File;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\File\FileService;
use App\Service\Validation\ValidationService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;

use App\Util\StringHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentRecordService extends BaseService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly ValidationService $validationService,
        private readonly FileService $fileService,
        private readonly NotificationEventDispatcher $notificationDispatcher
    ) {
    }

    public function create(Document $document, array $data, User $user): DocumentRecord
    {
        $this->validateFields($data, $user);
        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();

            $documentRecord = new DocumentRecord(\array_merge($this->prepareFields($data), ['createdBy' => $user]));

            if (isset($data['files']) && is_object($data['files']) && $data['files']->get('files') ?? null) {
                foreach ($data['files']->get('files') as $file) {
                    $fileEntity = $this->fileService->uploadVehicleDocumentFile($file, $user);
                    $documentRecord->addFile($fileEntity);
                }
            }
            $document->addRecord($documentRecord);

            $this->em->persist($documentRecord);
            $this->em->flush();

            $documentRecord->setStatus($this->calculateStatus($documentRecord));

            $connection->commit();

            if ($documentRecord->getFilesArray() && $documentRecord->getDocument()) {
                if ($documentRecord->getDocument()->isVehicleDocument()) {
                    $this->notificationDispatcher->dispatch(Event::DOCUMENT_RECORD_ADDED, $documentRecord);
                } elseif ($documentRecord->getDocument()->isDriverDocument()) {
                    $this->notificationDispatcher->dispatch(Event::DRIVER_DOCUMENT_RECORD_ADDED, $documentRecord);
                } elseif ($documentRecord->getDocument()->isAssetDocument()) {
                    $this->notificationDispatcher->dispatch(Event::ASSET_DOCUMENT_RECORD_ADDED, $documentRecord);
                }
            }

            return $documentRecord;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }

            throw $e;
        }
    }

    private function prepareFields(array $data): array
    {
        if (!empty($data['issueDate'])) {
            $data['issueDate'] = self::parseDateToUTC($data['issueDate']);
        } else {
            $data['issueDate'] = null;
        }

        if (!empty($data['expDate'])) {
            $data['expDate'] = self::parseDateToUTC($data['expDate']);
        } else {
            $data['expDate'] = null;
        }

        if (!empty($data['cost'])) {
            $data['cost'] = (float)$data['cost'];
        } else {
            $data['cost'] = null;
        }

        if ($data['noExpiry'] ?? null) {
            $data['noExpiry'] = StringHelper::stringToBool($data['noExpiry']);
        }

        return $data;
    }

    public function validateFields(array $fields, User $currentUser)
    {
        $errors = [];

        if ($fields['issueDate'] ?? null) {
            $errors = $this->validationService->validateDate($fields, 'issueDate', $errors);
        }

        if ($fields['expDate'] ?? null) {
            $errors = $this->validationService->validateDate($fields, 'expDate', $errors);
        }

        if ($fields['cost'] ?? null) {
            if (!is_numeric($fields['cost'])) {
                $errors['cost'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
            } elseif ($fields['cost'] != abs($fields['cost'])) {
                $errors['cost'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function edit(DocumentRecord $documentRecord, array $data, User $user): DocumentRecord
    {
        $this->validateFields($data, $user);
        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();

            $documentRecord->setAttributes(
                \array_merge($this->prepareFields($data),
                    ['updatedAt' => new \DateTime(), 'updatedBy' => $user, 'createdBy' => $user])
            );

            if ($data['removeFiles'] ?? null) {
                $documentRecord->removeFiles($data['removeFiles']);

                foreach ($data['removeFiles'] as $fileId) {
                    $file = $this->em->getRepository(File::class)->find($fileId);

                    if ($file) {
                        $this->fileService->delete($file);
                        $this->em->remove($file);
                    }
                }
            }

            if (isset($data['files']) && is_object($data['files']) && $data['files']->get('files') ?? null) {
                foreach ($data['files']->get('files') as $file) {
                    $fileEntity = $this->fileService->uploadVehicleDocumentFile($file, $user);
                    $documentRecord->addFile($fileEntity);
                }
            }
            $documentRecord->setStatus($this->calculateStatus($documentRecord));

            $this->em->flush();

            $connection->commit();

            return $documentRecord;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    public function calculateStatus(
        DocumentRecord $documentRecord,
        string $defaultStatus = DocumentRecord::STATUS_ACTIVE
    ): string {
        if (!in_array($defaultStatus, DocumentRecord::ALLOWED_STATUSES, true)) {
            throw new \Exception('Invalid status');
        }

        if ($documentRecord->getNoExpiry()) {
            return DocumentRecord::STATUS_ACTIVE;
        }

        $nowTs = (new \DateTime())->getTimestamp();

        $notifyDate = $documentRecord->getNotifyDate();

        if (null === $notifyDate) {
            return DocumentRecord::STATUS_ACTIVE;
        }

        if (null === $documentRecord->getExpDate() || $nowTs < $notifyDate->getTimestamp()) {
            return DocumentRecord::STATUS_ACTIVE;
        }

        if ($nowTs >= $notifyDate->getTimestamp() && $nowTs < $documentRecord->getExpDate()->getTimestamp()) {
            if ($documentRecord->getStatus() !== DocumentRecord::STATUS_EXPIRE_SOON) {
                if ($documentRecord->getDocument()->isVehicleDocument()) {
                    $this->notificationDispatcher->dispatch(Event::DOCUMENT_EXPIRE_SOON, $documentRecord);
                } elseif ($documentRecord->getDocument()->isDriverDocument()) {
                    $this->notificationDispatcher->dispatch(Event::DRIVER_DOCUMENT_EXPIRE_SOON, $documentRecord);
                } elseif ($documentRecord->getDocument()->isAssetDocument()) {
                    $this->notificationDispatcher->dispatch(Event::ASSET_DOCUMENT_EXPIRE_SOON, $documentRecord);
                }
            }

            return DocumentRecord::STATUS_EXPIRE_SOON;
        }

        if ($nowTs >= $documentRecord->getExpDate()->getTimestamp()) {
            if ($documentRecord->getStatus() !== DocumentRecord::STATUS_EXPIRED) {
                if ($documentRecord->getDocument()->isVehicleDocument()) {
                    $this->notificationDispatcher->dispatch(Event::DOCUMENT_EXPIRED, $documentRecord);
                } elseif ($documentRecord->getDocument()->isDriverDocument()) {
                    $this->notificationDispatcher->dispatch(Event::DRIVER_DOCUMENT_EXPIRED, $documentRecord);
                } elseif ($documentRecord->getDocument()->isAssetDocument()) {
                    $this->notificationDispatcher->dispatch(Event::ASSET_DOCUMENT_EXPIRED, $documentRecord);
                }
            }

            return DocumentRecord::STATUS_EXPIRED;
        }

        return $defaultStatus;
    }
}