<?php

namespace App\Service\File;

use App\Entity\Chat;
use App\Entity\File as FileEntity;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalFileService extends FileService
{
    public const AVATAR_PUBLIC_PATH = 'uploads/avatars/';
    public const VEHICLE_PICTURE_PATH = self::APP_WEB_PATH . self::VEHICLE_PUBLIC_PATH;
    public const VEHICLE_PUBLIC_PATH = 'uploads/vehicle/pictures/';
    public const AVATAR_PATH = self::APP_WEB_PATH . 'uploads/avatars/';
    public const SERVICE_RECORD_PATH = self::APP_WEB_PATH . self::SERVICE_RECORD_PUBLIC_PATH;
    public const SERVICE_RECORD_PUBLIC_PATH = 'uploads/service_records/';
    public const AVATAR_EXTENSIONS = ['png', 'jpg', 'jpeg'];
    public const VEHICLE_DOCUMENT_PATH = self::APP_WEB_PATH . self::VEHICLE_DOCUMENT_PUBLIC_PATH;
    public const VEHICLE_DOCUMENT_PUBLIC_PATH = 'uploads/documents/';
    public const INSTALLATION_PATH = self::APP_WEB_PATH . self::INSTALLATION_PUBLIC_PATH;
    public const INSTALLATION_PUBLIC_PATH = 'uploads/installation/';
    public const FUEL_CARD_DOCUMENT_PATH = self::APP_WEB_PATH . self::FUEL_CARD_DOCUMENT_PUBLIC_PATH;
    public const FUEL_CARD_DOCUMENT_PUBLIC_PATH = 'uploads/fuelCard/';
    public const SFTP_FUEL_DOCUMENT_PATH = self::APP_WEB_PATH . self::SFTP_FUEL_DOCUMENT_PUBLIC_PATH;
    public const SFTP_FUEL_DOCUMENT_PUBLIC_PATH = 'uploads/sftpFuelFile/';
    public const FUEL_CARD_EXTENSIONS = ['csv', 'xlsx', 'xls', 'txt'];
    public const DEVICES_VEHICLES_DOCUMENT_PATH = self::APP_WEB_PATH . self::DEVICES_VEHICLES_DOCUMENT_PUBLIC_PATH;
    public const DEVICES_VEHICLES_DOCUMENT_PUBLIC_PATH = 'uploads/devicesVehicles/';
    public const DEVICES_VEHICLES_EXTENSIONS = ['csv'];
    public const INSPECTION_FORM_PATH = self::APP_WEB_PATH . self::INSPECTION_FORM_PUBLIC_PATH;
    public const INSPECTION_FORM_PUBLIC_PATH = 'uploads/inspection_form/';
    public const DIGITAL_FORM_PATH = self::APP_WEB_PATH . self::DIGITAL_FORM_PUBLIC_PATH;
    public const DIGITAL_FORM_PUBLIC_PATH = 'uploads/digital_form/';
    public const RESELLER_LOGO_PATH = self::APP_WEB_PATH . self::RESELLER_LOGO_PATH_PUBLIC_PATH;
    public const RESELLER_LOGO_PATH_PUBLIC_PATH = 'uploads/reseller/';
    public const RESELLER_LOGO_EXTENSIONS = ['png', 'jpg', 'jpeg', 'ico', 'svg'];
    public const VEHICLE_TYPE_PATH = self::APP_WEB_PATH . self::VEHICLE_TYPE_PUBLIC_PATH;
    public const VEHICLE_TYPE_PUBLIC_PATH = 'uploads/vehicle_type/';
    public const CHAT_ATTACHMENTS_PATH = self::APP_WEB_UPLOAD_PATH . self::CHAT_ATTACHMENTS_FOLDER_PATH;
    public const CHAT_ATTACHMENTS_THUMB_PATH = self::CHAT_ATTACHMENTS_PATH . self::THUMB_FOLDER_PATH;

    /**
     * @param string $filename
     * @return void
     */
    private function deleteSourceFile(string $filename)
    {
        (new Filesystem())->remove($filename);
    }

    /**
     * @param string $filename
     * @return string|bool
     */
    private function fetchSourceFile(string $filename)
    {
        return file_exists($filename) ? file_get_contents($filename) : false;
    }

    /**
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param ImageService $imageService
     * @param RouterInterface $router
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        ImageService $imageService,
        RouterInterface $router
    ) {
        parent::__construct($translator, $em, $imageService, $router);
    }

    /**
     * @param UploadedFile $file
     * @param string $path
     * @param array|null $extensions
     * @param User|null $currentUser
     * @param bool $originalExtension
     * @return FileEntity
     * @throws \Exception
     */
    public function upload(
        UploadedFile $file,
        string $path,
        array $extensions = null,
        User $currentUser = null,
        $originalExtension = false
    ): FileEntity {
        $fileName = $this->getUniqueFileName($file, $originalExtension);
        $file->move($path, $fileName);
        $fileEntity = new FileEntity($fileName, $path);

        if ($extensions && !in_array($fileEntity->getExtension($fileName), $extensions)) {
            throw new \Exception($this->translator->trans('validation.errors.image.type_is_not_allowed'));
        }
        $fileEntity->setDisplayName($file->getClientOriginalName());
        $fileEntity->setMimeType(self::getMimeType($fileEntity->getFullPath()));
        $fileEntity->setSize(filesize($fileEntity->getFullPath()));

        if ($currentUser) {
            $fileEntity->setCreatedBy($currentUser);
        }

        $this->em->persist($fileEntity);
        $this->em->flush();

        return $fileEntity;
    }

    /**
     * @param UploadedFile $file
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadAvatar(UploadedFile $file): FileEntity
    {
        return $this->upload($file, self::AVATAR_PATH, self::AVATAR_EXTENSIONS);
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadServiceRecordFile(UploadedFile $file, User $currentUser): FileEntity
    {
        return $this->upload($file, self::SERVICE_RECORD_PATH, null, $currentUser);
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadVehicleDocumentFile(UploadedFile $file, User $currentUser)
    {
        return $this->upload($file, self::VEHICLE_DOCUMENT_PATH, null, $currentUser);
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadVehiclePictureFile(UploadedFile $file, User $currentUser)
    {
        return $this->upload($file, self::VEHICLE_PICTURE_PATH, null, $currentUser);
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadDeviceInstallationFile(UploadedFile $file, User $currentUser)
    {
        return $this->upload($file, self::INSTALLATION_PATH, null, $currentUser);
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadFuelCardFile(UploadedFile $file, User $currentUser): FileEntity
    {
        return $this->upload($file, self::FUEL_CARD_DOCUMENT_PATH, self::FUEL_CARD_EXTENSIONS, $currentUser, true);
    }

    /**
     * @param UploadedFile $file
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadSFTPFuelFile(UploadedFile $file): FileEntity
    {
        return $this->upload($file, self::SFTP_FUEL_DOCUMENT_PATH, self::FUEL_CARD_EXTENSIONS, null, true);
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadDevicesVehiclesFile(UploadedFile $file, User $currentUser): FileEntity
    {
        return $this->upload(
            $file,
            self::DEVICES_VEHICLES_DOCUMENT_PATH,
            self::DEVICES_VEHICLES_EXTENSIONS,
            $currentUser,
            true
        );
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadInspectionFormFile(UploadedFile $file, User $currentUser)
    {
        return $this->upload($file, self::INSPECTION_FORM_PATH, null, $currentUser);
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadDigitalFormFile(UploadedFile $file, User $currentUser)
    {
        return $this->upload($file, self::DIGITAL_FORM_PATH, null, $currentUser);
    }

    /**
     * @param UploadedFile $file
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadResellerLogo(UploadedFile $file): FileEntity
    {
        return $this->upload($file, self::RESELLER_LOGO_PATH, self::RESELLER_LOGO_EXTENSIONS);
    }

    public function uploadVehicleTypePictureFile(UploadedFile $file, ?User $currentUser)
    {
        return $this->upload($file, self::VEHICLE_TYPE_PATH, null, $currentUser);
    }

    /**
     * @inheritDoc
     */
    public function uploadChatAttachment(UploadedFile $file, Chat $chat, ?User $currentUser = null): FileEntity
    {
        $file = $this->upload($file, self::CHAT_ATTACHMENTS_PATH . $chat->getId() . '/', null, $currentUser, true);
        $file->setUrl($this->generateUrlForChatAttachment($chat, $file));

        if (self::isImage($file->getFullPath())) {
            $file = $this->createChatThumbnail($file);
            $file->setUrl($this->generateUrlForChatAttachment($chat, $file));
        }

        return $file;
    }

    /**
     * @inheritDoc
     */
    public function deleteSource(FileEntity $file): bool
    {
        if (!$file->getRemotePath()) {
            $path = $file->isOriginal() ? $file->getFullPath() : $file->getPath();
            $this->deleteSourceFile($path);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function fetchSource(FileEntity $file): Response
    {
        if (!$file->getRemotePath()) {
            return new BinaryFileResponse($file->getPath() . $file->getName());
        }

        return new Response();
    }
}