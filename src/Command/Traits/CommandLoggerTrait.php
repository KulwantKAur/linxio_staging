<?php

namespace App\Command\Traits;

trait CommandLoggerTrait
{
    public function logException(\Throwable $exception, $extra = [])
    {
        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $context = ['exception' => $exception];

        if ($extra) {
            $context['extra'] = $extra;
        }

        $this->logger->critical($message, $context);
    }
}