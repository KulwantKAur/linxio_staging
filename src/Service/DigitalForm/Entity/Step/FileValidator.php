<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidator extends AbstractStepValidator
{
    /**
     * @inheritDoc
     */
    public function isValid(array $stepOptions, $data = null): bool
    {
        return ($data instanceof UploadedFile) && $data->isValid() && ($data->getSize() > 0);
    }
}
