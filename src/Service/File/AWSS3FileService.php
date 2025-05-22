<?php

namespace App\Service\File;

use App\Entity\Chat;
use App\Entity\File as FileEntity;
use App\Entity\User;
use Aws\Credentials\Credentials;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AWSS3FileService extends FileService
{
    public const CHAT_ATTACHMENTS_THUMB_PATH = self::CHAT_ATTACHMENTS_FOLDER_PATH . self::THUMB_FOLDER_PATH;

    private S3Client $s3;
    private string $bucket;

    /**
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param ImageService $imageService
     * @param RouterInterface $router
     * @param string $key
     * @param string $secret
     * @param string $bucket
     * @param string $region
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        ImageService $imageService,
        RouterInterface $router,
        string $key,
        string $secret,
        string $bucket,
        string $region
    ) {
        parent::__construct($translator, $em, $imageService, $router);
        $this->bucket = $bucket;
        $credentials = new Credentials($key, $secret);
        $this->s3 = new S3Client(
            [
                'version' => 'latest',
                'region' => $region,
                'credentials' => $credentials
            ]
        );
    }

    /**
     * @param string $remotePath
     * @return string|null
     * @throws \Exception
     */
    public function fetchObject(string $remotePath): ?string
    {
        try {
            $result = $this->s3->getObject([
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
            ]);
            $this->s3->waitUntil('ObjectExists', ['Bucket' => $this->bucket, 'Key' => $remotePath]);
            header("Content-Type: {$result['ContentType']}");
        } catch (S3Exception $e) {
            throw new \Exception('There was an error fetching the file');
        }

        return $result['Body'] ?? null;
    }

    /**
     * @param string $remotePath
     * @param string $filePath
     * @return string
     * @throws \Exception
     */
    public function putObject(string $remotePath, string $filePath): ?string
    {
        try {
            $settings = [
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
                'SourceFile' => $filePath,
            ];

            if ($contentType = self::getMimeType($filePath)) {
                $settings['ContentType'] = $contentType;
            }

            $result = $this->s3->putObject($settings);
            $this->s3->waitUntil('ObjectExists', ['Bucket' => $this->bucket, 'Key' => $remotePath]);
        } catch (S3Exception $e) {
            throw new \Exception('There was an error uploading the file ' . $remotePath);
        }

        return $result['ObjectURL'] ?? null;
    }

    /**
     * @param string $remotePath
     * @return bool
     * @throws \Exception
     */
    public function deleteObject(string $remotePath): bool
    {
        try {
            $result = $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $remotePath
            ]);
            $this->s3->waitUntil('ObjectNotExists', ['Bucket' => $this->bucket, 'Key' => $remotePath]);

            if (!isset($result['DeleteMarker'])) {
                throw new \Exception('There was an error deleting the file ' . $remotePath);
            }
        } catch (S3Exception $e) {
            throw new \Exception('There was an error deleting the file ' . $remotePath);
        }

        return true;
    }

    /**
     * @param string $remotePath
     * @return string|null
     * @throws \Exception
     */
    public function putFolder(string $remotePath): ?string
    {
        try {
            $result = $this->s3->putObject(['Bucket' => $this->bucket, 'Key' => $remotePath]);
            $this->s3->waitUntil('ObjectExists', ['Bucket' => $this->bucket, 'Key' => $remotePath]);
        } catch (S3Exception $e) {
            throw new \Exception('There was an error uploading the folder ' . $remotePath);
        }

        return $result['ObjectURL'] ?? null;
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
        $tempFilePathWithName = self::getTempFilePathWithName($file);
        $result = $this->putObject($path . $fileName, $tempFilePathWithName);
        $fileEntity = new FileEntity($fileName, $path);
        $fileEntity->setDisplayName($file->getClientOriginalName());
        $fileEntity->setMimeType(self::getMimeType($tempFilePathWithName));
        $fileEntity->setRemotePath($result);
        $fileEntity->setSize(filesize($tempFilePathWithName));

        if ($extensions && !in_array($fileEntity->getExtension($fileName), $extensions)) {
            throw new \Exception($this->translator->trans('validation.errors.image.type_is_not_allowed'));
        }
        if ($currentUser) {
            $fileEntity->setCreatedBy($currentUser);
        }

        $this->em->persist($fileEntity);
        $this->em->flush();

        return $fileEntity;
    }

    /**
     * @inheritDoc
     */
    public function uploadChatAttachment(UploadedFile $file, Chat $chat, ?User $currentUser = null): FileEntity
    {
        $tempFilePathWithName = self::getTempFilePathWithName($file);
        $file = $this->upload($file, self::CHAT_ATTACHMENTS_FOLDER_PATH . $chat->getId() . '/', null, $currentUser, true);
        $file->setUrl($this->generateUrlForChatAttachment($chat, $file));

        if (self::isImage($tempFilePathWithName)) {
            $file = $this->createChatThumbnail($file, $tempFilePathWithName);
            $file->setUrl($this->generateUrlForChatAttachment($chat, $file));
            $this->clearChatTempThumbnail($file);
        }

        return $file;
    }

    /**
     * @inheritDoc
     */
    public function deleteSource(FileEntity $file): bool
    {
        if ($file->getRemotePath()) {
            return $this->deleteObject($file->getFullPath());
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function fetchSource(FileEntity $file): Response
    {
        if ($file->getRemotePath()) {
            $fileSource = $this->fetchObject($file->getPath() . $file->getName());
            echo $fileSource;
        }

        return new Response();
    }

    /**
     * @param FileEntity $file
     * @return bool
     * @throws \Exception
     */
    public function clearChatTempThumbnail(FileEntity $file)
    {
        try {
            $url = $this->imageService->resolve($file->getOriginal()->getName(), self::CHAT_THUMB_FILTER_NAME);
            $tempThumbPath = self::APP_WEB_PATH . parse_url($url, PHP_URL_PATH);

            return unlink($tempThumbPath);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}