<?php

namespace App\Events\DigitalForm;

use App\Entity\DigitalForm;
use App\Entity\Vehicle;
use Symfony\Contracts\EventDispatcher\Event;

class DigitalFormEvent extends Event
{
    /** @var string */
    public const FORM_CREATE = 'app.event.digitalForm.formCreate';
    public const FORM_DELETE = 'app.event.digitalForm.formDelete';
    public const FORM_EDIT = 'app.event.digitalForm.formEdit';
    public const FORM_GET = 'app.event.digitalForm.formGet';
    public const FORM_RESTORE = 'app.event.digitalForm.formRestore';

    /** @var DigitalForm */
    protected $form;

    /** @var Vehicle */
    protected $vehicle;


    public function __construct(DigitalForm &$form, Vehicle $vehicle = null)
    {
        $this->form = $form;
        $this->vehicle = $vehicle;
    }

    public function getForm(): DigitalForm
    {
        return $this->form;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }
}
