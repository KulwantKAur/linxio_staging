<?php

namespace App\Util;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

class AwsS3ForPrism
{
    public const REGION = 'ap-southeast-2';

    private $s3;
    private $bucket;

    /**
     * @param string $key
     * @param string $secret
     * @param string $bucket
     */
    public function __construct(string $key, string $secret, string $bucket)
    {
        $this->bucket = $bucket;
        $credentials = new Credentials($key, $secret);
        $this->s3 = new S3Client(
            [
                'version' => 'latest',
                'region' => self::REGION,
                'credentials' => $credentials
            ]
        );
    }

    /**
     * @param string $path
     * @param string $name
     * @return string
     */
    public function putObject(string $path, string $name): string
    {
        $result = $this->s3->putObject(['Bucket' => $this->bucket, 'Key' => $name, 'SourceFile' => $path]);
        $this->s3->waitUntil('ObjectExists', ['Bucket' => $this->bucket, 'Key' => $name]);

        return $result['ObjectURL'];
    }

    /**
     * @param string $key
     * @return string
     */
    public function putFolder(string $key): string
    {
        $result = $this->s3->putObject(['Bucket' => $this->bucket, 'Key' => $key]);
        $this->s3->waitUntil('ObjectExists', ['Bucket' => $this->bucket, 'Key' => $key]);

        return $result['ObjectURL'];
    }
}