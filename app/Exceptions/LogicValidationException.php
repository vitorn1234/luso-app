<?php

namespace App\Exceptions;

use Exception;

class LogicValidationException extends Exception
{
    protected $errors;

    public function __construct($message = "", $errors = [], $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function errors()
    {
        return $this->errors;
    }
}
