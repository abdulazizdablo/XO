<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class OutOfStockException extends Exception
{
    protected $data;

    public function __construct($data = null, $message = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
