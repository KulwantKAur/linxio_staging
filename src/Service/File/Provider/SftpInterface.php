<?php

namespace App\Service\File\Provider;

interface SftpInterface
{
    /**
     * Gets all files in a directory.
     *
     * @param string $path
     * @return array
     */
    public function getDirectoryList(string $path): array;

    /**
     * Checks if file is a directory.
     *
     * @param string $filePath
     * @return bool
     */
    public function isDir(string $filePath): bool;

    /**
     * Check if file exists.
     *
     * @param string $filePath
     * @return bool
     */
    public function fileExists(string $filePath): bool;

    /**
     * Disconnect from SFTP.
     */
    public function disconnect();
}
