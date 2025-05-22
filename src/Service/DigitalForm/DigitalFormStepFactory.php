<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm;

use App\Exceptions\DigitalFormStepFactoryException;
use App\Service\DigitalForm\Entity\Step\Date;
use App\Service\DigitalForm\Entity\Step\Datetime;
use App\Service\DigitalForm\Entity\Step\DatetimeValidator;
use App\Service\DigitalForm\Entity\Step\DateValidator;
use App\Service\DigitalForm\Entity\Step\File;
use App\Service\DigitalForm\Entity\Step\FileValidator;
use App\Service\DigitalForm\Entity\Step\Image;
use App\Service\DigitalForm\Entity\Step\ImageValidator;
use App\Service\DigitalForm\Entity\Step\ListMulti;
use App\Service\DigitalForm\Entity\Step\ListMultiValidator;
use App\Service\DigitalForm\Entity\Step\ListSingle;
use App\Service\DigitalForm\Entity\Step\ListSingleValidator;
use App\Service\DigitalForm\Entity\Step\NumberFloat;
use App\Service\DigitalForm\Entity\Step\NumberFloatValidator;
use App\Service\DigitalForm\Entity\Step\NumberInt;
use App\Service\DigitalForm\Entity\Step\NumberIntValidator;
use App\Service\DigitalForm\Entity\Step\Signature;
use App\Service\DigitalForm\Entity\Step\SignatureValidator;
use App\Service\DigitalForm\Entity\Step\TextMulti;
use App\Service\DigitalForm\Entity\Step\TextMultiValidator;
use App\Service\DigitalForm\Entity\Step\TextSingle;
use App\Service\DigitalForm\Entity\Step\Odometer;
use App\Service\DigitalForm\Entity\Step\OdometerValidator;
use App\Service\DigitalForm\Entity\Step\TextSingleValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class DigitalFormStepFactory
{
    /** @var string */
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_FILE = 'file';
    public const TYPE_IMAGE = 'image';
    public const TYPE_LIST_MULTI = 'list.multi';
    public const TYPE_LIST_SINGLE = 'list.single';
    public const TYPE_NUMBER_FLOAT = 'number.float';
    public const TYPE_NUMBER_INT = 'number.int';
    public const TYPE_ODOMETER = 'odometer';
    public const TYPE_SIGNATURE = 'signature';
    public const TYPE_TEXT_MULTI = 'text.multi';
    public const TYPE_TEXT_SINGLE = 'text.single';

    /** @var TranslatorInterface */
    private $translator;


    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @throws DigitalFormStepFactoryException
     */
    public function create(string $type = null)
    {
        switch ($type) {
            case self::TYPE_DATE:
                return new Date();
            case self::TYPE_DATETIME:
                return new Datetime();
            case self::TYPE_FILE:
                return new File();
            case self::TYPE_IMAGE:
                return new Image();
            case self::TYPE_LIST_MULTI:
                return new ListMulti();
            case self::TYPE_LIST_SINGLE:
                return new ListSingle();
            case self::TYPE_NUMBER_FLOAT:
                return new NumberFloat();
            case self::TYPE_NUMBER_INT:
                return new NumberInt();
            case self::TYPE_ODOMETER:
                return new Odometer();
            case self::TYPE_SIGNATURE:
                return new Signature();
            case self::TYPE_TEXT_MULTI:
                return new TextMulti();
            case self::TYPE_TEXT_SINGLE:
                return new TextSingle();
            default:
                throw new DigitalFormStepFactoryException($this->translator->trans('digitalForm.answerFactory.stepObjectInvalidType', ['%type%' => $type]));
        }
    }

    /**
     * @throws DigitalFormStepFactoryException
     */
    public function createValidator(string $type)
    {
        switch ($type) {
            case self::TYPE_DATE:
                return new DateValidator();
            case self::TYPE_DATETIME:
                return new DatetimeValidator();
            case self::TYPE_FILE:
                return new FileValidator();
            case self::TYPE_IMAGE:
                return new ImageValidator();
            case self::TYPE_LIST_MULTI:
                return new ListMultiValidator();
            case self::TYPE_LIST_SINGLE:
                return new ListSingleValidator();
            case self::TYPE_NUMBER_FLOAT:
                return new NumberFloatValidator();
            case self::TYPE_NUMBER_INT:
                return new NumberIntValidator();
            case self::TYPE_ODOMETER:
                return new OdometerValidator();
            case self::TYPE_SIGNATURE:
                return new SignatureValidator();
            case self::TYPE_TEXT_MULTI:
                return new TextMultiValidator();
            case self::TYPE_TEXT_SINGLE:
                return new TextSingleValidator();
            default:
                throw new DigitalFormStepFactoryException($this->translator->trans('digitalForm.answerFactory.stepValidatorInvalidType', ['%type%' => $type]));
        }
    }
}
