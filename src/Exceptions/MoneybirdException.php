<?php

namespace Kobalt\LaravelMoneybird\Exceptions;

use Exception;

class MoneybirdException extends Exception
{
    protected array $errors = [];

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}