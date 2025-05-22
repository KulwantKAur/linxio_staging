<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity;

class Condition implements \JsonSerializable
{
    /** @var string */
    public const OPERATOR_LESS          = '<';
    public const OPERATOR_LESS_OR_EQUAL = '>=';
    public const OPERATOR_MORE          = '>';
    public const OPERATOR_MORE_OR_EQUAL = '>=';
    public const OPERATOR_EQUAL         = '==';

    /** @var int */
    private $questionId;

    /** @var string */
    private $operator;

    /** @var int */
    private $value;


    public function __construct($questionId, $operator, $value)
    {
        $this->questionId = (int) $questionId;
        $this->operator = (string) $operator;
        $this->value = (int) $value;
    }

    public static function fromJson(string $json = null): ?Condition
    {
        if ($json === null) {
            return null;
        }

        $data = json_decode($json);
        if (empty($data['questionId']) || $data['operator'] || $data['value']) {
            return null;
        }

        return new self($data['questionId'], $data['operator'], $data['value']);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'questionId' => $this->questionId,
            'operator' => $this->operator,
            'value' => $this->value,
        ];
    }
}
