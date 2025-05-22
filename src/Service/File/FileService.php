<?php

namespace App\Service\File;

use App\Entity\Chat;
use App\Entity\File;
use App\Entity\File as FileEntity;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class FileService implements FileServiceInterface
{
    public const APP_WEB_PATH = '/srv/web/';
    public const APP_WEB_UPLOAD_PATH = self::APP_WEB_PATH . 'uploads/';
    public const THUMB_FOLDER_PATH = '_thumb/';
    public const CHAT_ATTACHMENTS_FOLDER_PATH = 'chat_attachments/';
    public const CHAT_THUMB_FILTER_NAME = 'chat_thumb';

    /** @var TranslatorInterface */
    protected $translator;
    /** @var EntityManagerInterface */
    protected $em;
    protected ImageService $imageService;
    protected RouterInterface $router;

    /**
     * @param UploadedFile $file
     * @param string $path
     * @param array $extensions
     * @param User|null $currentUser
     * @param bool $originalExtension
     * @return File
     */
    abstract public function upload(
        UploadedFile $file,
        string $path,
        array $extensions = null,
        User $currentUser = null,
        $originalExtension = false
    ): File;

    /**
     * @param File $file
     * @return bool
     */
    abstract public function deleteSource(
        File $file
    ): bool;

    /**
     * @param File $file
     * @return Response
     */
    abstract public function fetchSource(
        File $file
    ): Response;

    /**
     * @param UploadedFile $file
     * @param Chat $chat
     * @param User|null $currentUser
     * @return File
     */
    abstract public function uploadChatAttachment(
        UploadedFile $file,
        Chat $chat,
        ?User $currentUser = null
    ): File;

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
        $this->translator = $translator;
        $this->em = $em;
        $this->imageService = $imageService;
        $this->router = $router;
    }

    /**
     * @param UploadedFile $file
     * @param bool $originalExtension
     * @return string
     */
    public function getUniqueFileName(UploadedFile $file, bool $originalExtension): string
    {
        $extension = $originalExtension
            ? $file->getClientOriginalExtension()
            : ($file->guessExtension() ?? $file->getClientOriginalExtension());

        return md5(uniqid()) . '.' . ($extension);
    }

    /**
     * @param FileEntity $file
     * @return bool
     */
    public function delete(FileEntity $file): bool
    {
        $result = $this->deleteSource($file);
        $fileOrig = $file->getOriginal();
        $fileThumb = $fileOrig ?: $file->getThumbnail();
        $result2 = $fileThumb ? $this->deleteSource($fileThumb) : true;
        $this->em->remove($file);

        if ($fileThumb) {
            $this->em->remove($fileThumb);
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param FileEntity $file
     * @return bool
     */
    public function deleteChatAttachmentByJob(FileEntity $file): bool
    {
        $fileOrig = $file->isOriginal() ? $file : $file->getOriginal();
        $fileThumb = $file->isOriginal() ? $file->getThumbnail() : $file;
        $result = $fileOrig ? $this->deleteSource($fileOrig) : false;
        $result2 = $fileThumb ? $this->deleteSource($fileThumb) : false;

        if ($fileOrig) {
            $fileOrig->setUrl(null);
//            $fileOrig->setPath('');
        }
        if ($fileThumb) {
            $fileThumb->setUrl(null);
//            $fileThumb->setPath('');
        }
        if ($fileOrig && $file->getId() != $fileOrig->getId()) {
            if ($fileThumb) {
                $fileThumb->setOriginal(null);
            }

            $this->em->remove($fileOrig);
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param int $id
     * @return FileEntity|null
     */
    public function getById(int $id): ?FileEntity
    {
        return $this->em->getRepository(FileEntity::class)->find($id);
    }

    /**
     * @param FileEntity $file
     * @param string|null $fileFullPath
     * @return FileEntity
     * @throws \Exception
     */
    public function createChatThumbnail(FileEntity $file, ?string $fileFullPath = null)
    {
        try {
            $fileFullPath = $fileFullPath ?? $file->getFullPath();
            $fileBinary = new FileBinary(
                $fileFullPath,
                self::getMimeType($fileFullPath),
                $file->getExtension()
            );
            $thumbBinary = $this->imageService->applyFilter($fileBinary, self::CHAT_THUMB_FILTER_NAME);
            $this->imageService->store($thumbBinary, $file->getName(), self::CHAT_THUMB_FILTER_NAME);
            $url = $this->imageService->resolve($file->getName(), self::CHAT_THUMB_FILTER_NAME);
            $thumbPath = parse_url($url, PHP_URL_PATH);
            $uploadedThumbFile = new UploadedFile(
                self::APP_WEB_PATH . $thumbPath,
                $file->getDisplayName(),
                self::getMimeType($fileFullPath),
                null,
                true
            );
            $thumbFileEntity = $this->upload(
                $uploadedThumbFile,
                static::CHAT_ATTACHMENTS_THUMB_PATH . $file->getId() . '/'
            );
            $thumbFileEntity->setOriginal($file);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        return $thumbFileEntity;
    }

    /**
     * @param Chat $chat
     * @param FileEntity $fileEntity
     * @return string
     */
    public function generateUrlForChatAttachment(Chat $chat, FileEntity $fileEntity): string
    {
        return 'https:' . $this->router->generate('chat_attachment_source', [
            'chatId' => $chat->getId(),
            'fileId' => $fileEntity->getId(),
            'filename' => $fileEntity->getDisplayName()
        ], UrlGeneratorInterface::NETWORK_PATH);
    }

    /**
     * @param string $filePath
     * @return string|null
     */
    public static function getMimeType(string $filePath): ?string
    {
        return file_exists($filePath) ? mime_content_type($filePath) : null;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public static function isImage(string $filePath): bool
    {
        return is_array(getimagesize($filePath));
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return string
     */
    public static function getTempFilePathWithName(UploadedFile $uploadedFile): string
    {
        return $uploadedFile->getPath() . '/' . $uploadedFile->getFilename();
    }
}