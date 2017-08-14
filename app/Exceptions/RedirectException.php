<?php

namespace App\Exceptions;

/**
 * Class RedirectException
 *
 * @package \App\Exceptions
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
