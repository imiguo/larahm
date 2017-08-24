<?php

namespace entimm\LaravelPayeer;

use Exception;

class AsmoneyException extends Exception
{
    protected $error;

    public function __construct($error)
    {
        parent::__construct();

        $this->message = is_array($error) ? implode(',', $error) : $error;
    }
}
