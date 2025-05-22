<?php

declare(strict_types=1);

namespace App\Service\File\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SFTP service to connect
 */
class SftpFileService implements SftpInterface
{
    protected TranslatorInterface $translator;
    protected EntityManagerInterface $em;
    protected ?SFTP $sftp;
    protected string $host;
    protected int $port = 22;
    protected string $user;
    protected string $privateKey;
    protected string|bool $password;
    private string $remotePath;
    private string $loadPath;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        string $host,
        string $user,
        string $privateKey,
        ?string $password,
        string $remotePath,
        string $loadPath
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->host = $host;
        $this->user = $user;
        $this->privateKey = $privateKey;
        $this->password = $password === 'false' ? false : $password;
        $this->remotePath = $remotePath;
        $this->loadPath = $loadPath;
    }

    /**
     * @return string
     */
    public function getRemotePath(): string
    {
        return $this->remotePath;
    }

    /**
     * @return string
     */
    public function getLoadPath(): string
    {
        return $this->loadPath;
    }

    /**
     * Gets the SFTP. Checks if connection already exists.
     *
     * @return SFTP
     * @throws Exception
     */
    protected function getSFTP(): SFTP
    {
        if (!isset($this->sftp)) {
            $this->sftp = $this->connect();
        }

        return $this->sftp;
    }

    /**
     * @return SFTP
     * @throws Exception
     */
    public function connect(): SFTP
    {
        $privateKey = file_get_contents(dirname(__DIR__) . $this->privateKey);
        $this->sftp = new SFTP($this->host);

        if ($privateKey != null) {
            $key = PublicKeyLoader::load($privateKey, $this->password);

            //Login using private key
            if (!$this->sftp->login($this->user, $key)) {
                throw new Exception(sprintf('SFTP authentication failed for user "%s" using private key', $this->user));
            }
        } elseif (!$this->sftp->login($this->user, $this->password)) {
            throw new Exception(sprintf('SFTP authentication failed for user "%s" using password', $this->user));
        }

        return $this->sftp;
    }

    /**
     * Checks if file is a directory.
     *
     * @param string $filePath
     * @return bool
     * @throws Exception
     */
    public function isDir(string $filePath): bool
    {
        return $this->getSFTP()->is_dir($filePath);
    }

    /**
     * Gets all files in a directory with details.
     *
     * @param string $path
     * @return array
     * @throws Exception
     */
    public function getDirectoryListDetails(string $path): array
    {
        return $this->getSFTP()->rawlist($path);
    }

    /**
     * Gets list all files in a directory without details.
     *
     * @param string $path
     * @return array
     * @throws Exception
     */
    public function getDirectoryList(string $path): array
    {
        return $this->getSFTP()->nlist($path);
    }


    /**
     * Downloads a file from the SFTP server.
     *
     * @param string $filePath
     * @param string|bool|resource|callable $localFile
     * @return string|false
     * @throws Exception
     */
    public function getRemoteFile(string $filePath, $localFile = false): bool|string
    {
        return $this->getSFTP()->get($filePath, $localFile);
    }

    /**
     * @param string $filePath
     * @param bool $recursive
     * @return string|false
     * @throws Exception
     */
    public function deleteRemoteFile(string $filePath, bool $recursive = true): bool|string
    {
        return $this->getSFTP()->delete($filePath, $recursive);
    }


    /**
     * Check if file exists.
     *
     * @param string $filePath
     * @return bool
     * @throws Exception
     */
    public function fileExists(string $filePath): bool
    {
        return $this->getSFTP()->file_exists($filePath);
    }

    /**
     * Get general information about a file.
     *
     * @param $filePath
     * @return array|false|int[]
     * @throws Exception
     */
    public function getFileStats($filePath): array|bool
    {
        return $this->getSFTP()->stat($filePath);
    }

    /**
     * Disconnect from SFTP.
     */
    public function disconnect()
    {
        $this->sftp?->disconnect();
        $this->sftp = null;
    }
}
