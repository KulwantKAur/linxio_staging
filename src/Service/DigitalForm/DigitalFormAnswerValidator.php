<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm;

use App\Entity\DigitalForm;
use App\Entity\DigitalFormStep;
use App\Service\DigitalForm\Entity\Answer;
use App\Service\DigitalForm\Entity\RawAnswer;
use Symfony\Contracts\Translation\TranslatorInterface;

class DigitalFormAnswerValidator
{
    /** @var DigitalFormStepFactory */
    private $stepFactory;

    /** @var Answer */
    private $answer;

    /** @var TranslatorInterface */
    private $translator;

    /** @var array */
    private $steps = [];

    /** @var array */
    private $errors = [];


    public function __construct(
        Answer $answer,
        DigitalFormStepFactory $stepFactory,
        TranslatorInterface $translator
    ) {
        $this->answer = $answer;
        $this->stepFactory = $stepFactory;
        $this->translator = $translator;
    }

    public function process(DigitalForm $form, RawAnswer $rawAnswer): void
    {
        $this->processDigitalForm($form);

        $sourceAnswer = $rawAnswer->getData();
        foreach ($sourceAnswer as $answerId => $answer) {
            $answerValue = $answer['value'];
            $step = $this->getStepById($answerId);
            if ($step === null) {
                $this->addError($answerId, $answerValue, $this->translator->trans('digitalForm.answerValidator.notPresent'));
                continue;
            }

            $stepOptions = $step->getOptions();
            $stepValidator = $this->stepFactory->createValidator($stepOptions['type']);

            if ($stepValidator->isValid($stepOptions, $answerValue) === false) {
                $this->addError($answerId, $answerValue, $this->translator->trans('digitalForm.answerValidator.nonValid'));
            } else {
                $this->answer->setAnswer($step, $answer);
            }
        }
    }

    public function isValid(): bool
    {
        return count($this->errors) === 0;
    }

    public function getValidAnswer(): Answer
    {
        return $this->answer;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function processDigitalForm(DigitalForm $form)
    {
        foreach ($form->getDigitalFormSteps() as $step) {
            $this->steps[$step->getId()] = $step;
        }
    }

    private function getStepById(int $stepId): ?DigitalFormStep
    {
        return $this->steps[$stepId] ?? null;
    }

    private function addError(int $answerId, $answerValue, string $errorMessage): void
    {
        $this->errors[] = [
            'id' => $answerId,
            'value' => $answerValue,
            'message' => $errorMessage,
        ];
    }
}
