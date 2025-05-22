<?php


namespace App\Util;


use App\Response\CsvResponse;

class FileHelper
{
    /**
     * @param string $path
     * @param string $filename
     * @param $content
     * @param bool $noCsvHeader
     * @return CsvResponse
     */
    public static function createCsvFile(string $path, string $filename, $content, $noCsvHeader = true)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $csv = new CsvResponse($content, 200, [], $noCsvHeader);
        file_put_contents($path . $filename, $csv->getContent());

        return $csv;
    }

    /**
     * @param $path
     * @return bool
     */
    public static function deletePath($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                self::deletePath(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } elseif (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }
}