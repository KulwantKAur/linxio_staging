<?php

namespace App\Exceptions;


class ValidationException extends \Exception
{
    protected $errors = [];
    protected $message = 'Validation error';

    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImplodedErrorsMessage(): string
    {
        $messages = [''];

        foreach ($this->getErrors() as $key => $value) {
            $messages[] = $key . ': ' . (is_array($value) ? implode(' ', $value) : $value);
        }

        return implode(' ', $messages);
    }

    public function addErrors(array $errors): self
    {
        $this->setErrors(array_merge($this->getErrors(), $errors));

        return $this;
    }
}