<?php

namespace entimm\LaravelPayeer;

use Exception;

class PayeerException extends Exception
{
    protected $error;

    public function __construct($error)
    {
        parent::__construct();

        $this->message = is_array($error) ? implode(',', $error) : $error;
    }
}
