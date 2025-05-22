<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.03.19
 * Time: 15:36
 */

namespace App\Service\File;


use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\File as FileEntity;

class LocalFileServiceTest extends LocalFileService
{
    /**
     * @param UploadedFile $file
     * @param string $path
     * @param array|null $extensions
     * @param User|null $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function upload(
        UploadedFile $file,
        string $path,
        array $extensions = null,
        User $currentUser = null,
        $originalExtension = false,
        $fileMove = false
    ): FileEntity {
        $fileName = $this->getUniqueFileName($file, $originalExtension);

        if ($fileMove) {
            $file->move($path, $fileName);
        }
        $fileEntity = new FileEntity($fileName, $path);
        $fileEntity->setDisplayName($file->getClientOriginalName());
        $fileEntity->setMimeType(self::getMimeType($fileEntity->getFullPath()));
        if ($currentUser) {
            $fileEntity->setCreatedBy($currentUser);
        }
        $this->em->persist($fileEntity);
        $this->em->flush();
        return $fileEntity;
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @return FileEntity
     * @throws \Exception
     */
    public function uploadFuelCardFile(UploadedFile $file, User $currentUser): FileEntity
    {
        return $this->upload($file, self::FUEL_CARD_DOCUMENT_PATH, self::FUEL_CARD_EXTENSIONS, $currentUser, true, true);
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
            true,
            true
        );
    }
}