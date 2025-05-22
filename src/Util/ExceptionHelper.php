<?php

namespace App\Util;


class ExceptionHelper
{
    /**
     * @param \Throwable $exception
     * @return false|string
     */
    public static function convertToJson(\Throwable $exception)
    {
        return json_encode([
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
    }
}