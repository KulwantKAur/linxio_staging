<?php

namespace App\Util;

class ApplicationVersionHelper
{
    private const MAJOR = 1;
    private const MINOR = 0;
    private const PATCH = 0;
    private const MASTER = 'master';
    private const DEVELOP = 'develop';

    /**
     * @param string $env
     * @return string
     */
    public static function getVersion(string $env)
    {
        $branch = $env == 'prod' ? self::MASTER : self::DEVELOP;

        try {
            $commitHash = self::parseCommitHash($branch);
        } catch (\Exception $exception) {
            $branch = $branch == self::MASTER ? self::DEVELOP : self::MASTER;

            try {
                $commitHash = self::parseCommitHash($branch);
            } catch (\Exception $exception) {
                return 'Unknown version';
            }
        }

        return sprintf('v%s.%s.%s-%s.%s',
            self::MAJOR,
            self::MINOR,
            self::PATCH,
            $branch,
            $commitHash
        );
    }

    /**
     * @return array
     */
    public static function getVersionFromTag(): array
    {
        try {
            $lastTag = exec('git tag -n', $tags);
            list($version, $description) = preg_split('/\s+/', $lastTag);

            return [
                'version' => $version,
                'description' => $description,
            ];
        } catch (\Exception $exception) {
            return [
                'error' => 'No recognized version'
            ];
        }
    }

    /**
     * @param string $branch
     * @return string
     */
    private static function parseCommitHash(string $branch)
    {
        return trim(file_get_contents(sprintf('../.git/refs/heads/%s', $branch)));
    }
}