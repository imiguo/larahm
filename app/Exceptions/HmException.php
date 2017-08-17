<?php

namespace App\Exceptions;

use Exception;

/**
 * Class HmException.
 */
class HmException extends Exception
{
    protected $response;

    public function __construct($response)
    {
        parent::__construct();

        $this->response = $response;
    }

    public function resolveResponse()
    {
        return $this->response;
    }
}
