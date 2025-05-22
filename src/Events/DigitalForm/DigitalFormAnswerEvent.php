<?php

namespace App\Events\DigitalForm;

use App\Entity\DigitalFormAnswer;
use Symfony\Contracts\EventDispatcher\Event;

class DigitalFormAnswerEvent extends Event
{
    /** @var string */
    public const ANSWER_CREATED = 'app.event.digitalForm.answerCreated';

    /** @var DigitalFormAnswer */
    protected $answer;


    public function __construct(DigitalFormAnswer &$answer)
    {
        $this->answer = $answer;
    }

    public function getAnswer(): DigitalFormAnswer
    {
        return $this->answer;
    }
}
