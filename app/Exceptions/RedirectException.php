<?php

namespace App\Exceptions;

/**
 * Class RedirectException.
 */
class RedirectException extends HmException
{
    public function __construct($url)
    {
        parent::__construct(redirect($url));
    }

    public function resolveResponse()
    {
        return $this->response;
    }
}
