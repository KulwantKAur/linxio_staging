<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

class DateValidator extends AbstractStepValidator
{
    /**
     * @inheritDoc
     */
    public function isValid(array $stepOptions, $data = null): bool
    {
        try {
            $date = new \DateTime($data);

            if (!empty($stepOptions['min'])) {
                $from = (new \DateTime($stepOptions['default']))->modify('- ' . $stepOptions['min'] . ' seconds');
                if ($date < $from) {
                    throw new \Exception();
                }
            }

            if (!empty($stepOptions['max'])) {
                $to = (new \DateTime($stepOptions['default']))->modify('+ ' . $stepOptions['max'] . ' seconds');
                if ($date > $to) {
                    throw new \Exception();
                }
            }

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
